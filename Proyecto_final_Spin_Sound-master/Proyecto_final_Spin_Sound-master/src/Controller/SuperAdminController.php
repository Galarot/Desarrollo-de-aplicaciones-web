<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;

class SuperAdminController extends AbstractController
{
    #[Route('/superadmin/panel', name: 'app_superadmin_panel')]
    public function panel(UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Obtener todos los usuarios excepto SUPER_ADMIN
        $todosUsuarios = $usuarioRepository->findAll();
        $usuarios = array_filter($todosUsuarios, function($usuario) {
            return !in_array('ROLE_SUPER_ADMIN', $usuario->getRoles());
        });
        
        return $this->render('superadmin/panel.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/superadmin/usuario/{id}/ban', name: 'app_superadmin_ban_user', methods: ['POST'])]
    public function banUser(int $id, UsuarioRepository $usuarioRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $usuario = $usuarioRepository->find($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // No permitir banear SUPER_ADMIN
        if (in_array('ROLE_SUPER_ADMIN', $usuario->getRoles())) {
            $this->addFlash('error', 'No se puede banear a un Super Admin');
            return $this->redirectToRoute('app_superadmin_panel');
        }

        $usuario->setDeleteAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Usuario baneado correctamente');
        return $this->redirectToRoute('app_superadmin_panel');
    }

    #[Route('/superadmin/usuario/{id}/unban', name: 'app_superadmin_unban_user', methods: ['POST'])]
    public function unbanUser(int $id, UsuarioRepository $usuarioRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $usuario = $usuarioRepository->find($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // Desbanear usuario
        $usuario->setDeleteAt(null);
        $em->flush();

        $this->addFlash('success', 'Usuario desbaneado correctamente');
        return $this->redirectToRoute('app_superadmin_panel');
    }

    #[Route('/superadmin/usuario/{id}/delete', name: 'app_superadmin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id, UsuarioRepository $usuarioRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $usuario = $usuarioRepository->find($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // No permitir eliminar SUPER_ADMIN
        if (in_array('ROLE_SUPER_ADMIN', $usuario->getRoles())) {
            $this->addFlash('error', 'No se puede eliminar a un Super Admin');
            return $this->redirectToRoute('app_superadmin_panel');
        }

        $em->remove($usuario);
        $em->flush();

        $this->addFlash('success', 'Usuario eliminado permanentemente');
        return $this->redirectToRoute('app_superadmin_panel');
    }

    #[Route('/superadmin/usuario/{id}/toggle-role/{role}', name: 'app_superadmin_toggle_role', methods: ['POST'])]
    public function toggleRole(int $id, string $role, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        // Validar CSRF token
        if (!$this->isCsrfTokenValid('toggle-role', $request->request->get('_token'))) {
            return $this->json(['error' => 'Token inválido'], 400);
        }
        
        $usuario = $usuarioRepository->find($id);
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        // No permitir gestionar usuarios SUPER_ADMIN
        if (in_array('ROLE_SUPER_ADMIN', $usuario->getRoles())) {
            return $this->json(['error' => 'No se pueden modificar los permisos de un Super Admin'], 403);
        }

        $roles = $usuario->getRoles();
        $roleToToggle = 'ROLE_' . strtoupper($role);
        
        if (in_array($roleToToggle, $roles)) {
            $roles = array_diff($roles, [$roleToToggle]);
        } else {
            $roles[] = $roleToToggle;
        }
        
        $usuario->setRoles(array_values($roles));
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/superadmin/usuarios/bulk-action', name: 'app_superadmin_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if (!$this->isCsrfTokenValid('bulk-action', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido');
            return $this->redirectToRoute('app_superadmin_panel');
        }

        $ids = $request->request->all('ids');
        $action = $request->request->get('action');

        if (empty($ids)) {
            $this->addFlash('error', 'No se seleccionaron usuarios');
            return $this->redirectToRoute('app_superadmin_panel');
        }

        $count = 0;
        foreach ($ids as $id) {
            $usuario = $usuarioRepository->find($id);
            if (!$usuario) continue;

            if ($action === 'ban') {
                $usuario->setDeleteAt(new \DateTimeImmutable());
                $count++;
            } elseif ($action === 'unban') {
                $usuario->setDeleteAt(null);
                $count++;
            } elseif ($action === 'delete') {
                $em->remove($usuario);
                $count++;
            }
        }

        $em->flush();

        $message = match($action) {
            'ban' => "$count usuarios baneados",
            'unban' => "$count usuarios desbaneados",
            'delete' => "$count usuarios eliminados permanentemente",
            default => "Acción completada"
        };
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_superadmin_panel');
    }
}
