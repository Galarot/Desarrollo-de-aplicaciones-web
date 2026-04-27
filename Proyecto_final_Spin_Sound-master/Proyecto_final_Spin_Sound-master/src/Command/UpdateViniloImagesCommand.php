<?php

namespace App\Command;

use App\Repository\ViniloRepository;
use App\Service\DiscogsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-vinilo-images',
    description: 'Actualiza las imágenes de los vinilos desde la API de Discogs',
)]
class UpdateViniloImagesCommand extends Command
{
    public function __construct(
        private readonly ViniloRepository $viniloRepository,
        private readonly DiscogsService $discogsService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Actualizando imágenes de vinilos desde Discogs');

        // Obtener todos los vinilos que tienen discogs_id pero no tienen imagen
        $vinilos = $this->viniloRepository->createQueryBuilder('v')
            ->where('v.discogs_id IS NOT NULL')
            ->andWhere('v.imagen IS NULL')
            ->getQuery()
            ->getResult();

        if (empty($vinilos)) {
            $io->success('No hay vinilos que necesiten actualización de imágenes.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($vinilos));

        $updated = 0;
        $failed = 0;

        foreach ($vinilos as $vinilo) {
            try {
                $releaseData = $this->discogsService->fetchRelease($vinilo->getDiscogsId());
                $imageUrl = $this->discogsService->getImageUrl($releaseData);

                if ($imageUrl) {
                    $vinilo->setImagen($imageUrl);
                    $this->entityManager->flush();
                    $updated++;
                    $io->progressAdvance();
                } else {
                    $failed++;
                    $io->progressAdvance();
                }

                // Pequeña pausa para no saturar la API
                usleep(250000); // 0.25 segundos
            } catch (\Throwable $e) {
                $failed++;
                $io->progressAdvance();
            }
        }

        $io->progressFinish();

        $io->success(sprintf(
            'Proceso completado. Actualizados: %d, Fallidos: %d',
            $updated,
            $failed
        ));

        return Command::SUCCESS;
    }
}

