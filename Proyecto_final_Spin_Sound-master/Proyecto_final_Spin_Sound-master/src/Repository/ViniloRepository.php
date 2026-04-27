<?php

namespace App\Repository;

use App\Entity\Vinilo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vinilo>
 */
class ViniloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vinilo::class);
    }

    public function findBySearch(string $query): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.artistas', 'a')
            ->where('LOWER(v.titulo) LIKE LOWER(:q) OR LOWER(a.nombre) LIKE LOWER(:q)')
            ->setParameter('q', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtra vinilos por búsqueda de texto, género, artista y precio máximo.
     * Todos los parámetros son opcionales.
     */
    public function findByFilters(
        string $query    = '',
        string $genero   = '',
        string $artista  = '',
        ?float $precioMax = null,
        string $orden    = ''
    ): array {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin('v.artistas', 'a')
            ->leftJoin('v.generos', 'g')
            ->addSelect('a', 'g');

        if ($query !== '') {
            $qb->andWhere('LOWER(v.titulo) LIKE LOWER(:q) OR LOWER(a.nombre) LIKE LOWER(:q)')
               ->setParameter('q', '%' . $query . '%');
        }

        if ($genero !== '') {
            $qb->andWhere('LOWER(g.nombre) = LOWER(:genero)')
               ->setParameter('genero', $genero);
        }

        if ($artista !== '') {
            $qb->andWhere('a.id = :artista_id')
               ->setParameter('artista_id', (int) $artista);
        }

        if ($precioMax !== null) {
            $qb->andWhere('v.precio <= :precio_max')
               ->setParameter('precio_max', $precioMax);
        }

        match ($orden) {
            'precio_asc'  => $qb->orderBy('v.precio', 'ASC'),
            'precio_desc' => $qb->orderBy('v.precio', 'DESC'),
            'novedad'     => $qb->orderBy('v.fecha_lanzamiento', 'DESC'),
            default       => $qb->orderBy('v.titulo', 'ASC'),
        };

        return $qb->getQuery()->getResult();
    }

    /**
     * Obtiene los vinilos más populares basándose en la cantidad de veces
     * que han sido añadidos a carritos.
     */
    public function findMostPopularByCart(int $limit = 4): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.detalleCarritos', 'dc')
            ->addSelect('COUNT(dc.id) as HIDDEN cart_count')
            ->groupBy('v.id')
            ->having('COUNT(dc.id) > 0')
            ->orderBy('cart_count', 'DESC')
            ->addOrderBy('v.fecha_lanzamiento', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
