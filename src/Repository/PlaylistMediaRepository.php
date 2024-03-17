<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Playlist;
use App\Entity\PlaylistMedia;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaylistMedia>
 *
 * @method PlaylistMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaylistMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaylistMedia[]    findAll()
 * @method PlaylistMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaylistMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistMedia::class);
    }

    public function getMediaForPlaylistAsQuery(Playlist $playlist)
    {
        return $this->getMediaForPlaylistBaseQuery($playlist)->getQuery();
    }

    public function getMediaForPlaylistBaseQuery(Playlist $playlist)
    {
        return $this->createQueryBuilder('pm')
            ->andWhere('pm.playlist = :playlist')
            ->setParameter('playlist', $playlist)
            ->orderBy('pm.showFrom', 'ASC');
    }

    public function getMediaForPlaylistByCustomTimeAndMedia(Playlist $playlist, bool $customTime = false, ?Media $media = null)
    {
        $qb = $this->getMediaForPlaylistBaseQuery($playlist)
            ->andWhere('pm.playlist = :playlist')
            ->andWhere('pm.customTime = :customTime')
            ->setParameter('playlist', $playlist)
            ->setParameter('customTime', $customTime);

        if ($media) {
            $qb->andWhere('pm.media != :media')
                ->setParameter('media', $media);
        }

        return $qb->getQuery()->getResult();
    }

    public function getMediaForPlaylistByShowTimeAndMedia(Playlist $playlist, ?DateTime $showFrom = null, ?DateTime $showto = null, ?PlaylistMedia $playlistMedia = null)
    {
        $qb = $this->getMediaForPlaylistBaseQuery($playlist)
            ->andWhere('pm.playlist = :playlist')
            ->setParameter('playlist', $playlist);

        if ($playlistMedia->getId()) {
            $qb->andWhere('pm.id != :playlistMedia')
                ->setParameter('playlistMedia', $playlistMedia->getId());
        }


        if ($showFrom && $showto) {
            //conditions covering time slot overlap
            $qb->andWhere($qb->expr()->orX(
            //checks if slot is within time of another multimedia or is around it
                $qb->expr()->orX(
                //checks if time slot is within another multimedia time slot
                    $qb->expr()->andX(
                        $qb->expr()->lte('pm.showFrom', ':showFrom'),
                        $qb->expr()->gte('pm.showTo', ':showTo')
                    ),
                    //checks if time slot is around another multimedia time slot
                    $qb->expr()->andX(
                        $qb->expr()->gte('pm.showFrom', ':showFrom'),
                        $qb->expr()->lte('pm.showTo', ':showTo')
                    )
                ),
                //checks if time slot is intersecting with another multimedia time slot
                $qb->expr()->orX(
                //checks lower boundaries
                    $qb->expr()->andX(
                        $qb->expr()->lt('pm.showFrom', ':showTo'),
                        //maybe or
                        $qb->expr()->gt('pm.showFrom', ':showFrom')
                    ),
                    //checks upper boundaries
                    $qb->expr()->andX(
                        $qb->expr()->gt('pm.showTo', ':showFrom'),
                        $qb->expr()->lt('pm.showTo', ':showTo')
                    )
                )
            ));
            $qb->setParameter('showFrom', $showFrom->format('H:i:s'))
                ->setParameter('showTo', $showto->format('H:i:s'));
        } else {
            if ($showFrom) {
                $qb->andWhere('pm.showFrom < :showFrom')
                    ->andWhere(' pm.showTo > :showFrom')
                    ->setParameter('showFrom', $showFrom->format('H:i:s'));
            }

            if ($showto) {
                $qb->andWhere('pm.showFrom < :showTo')
                    ->andWhere(' pm.showTo > :showTo')
                    ->setParameter('showTo', $showto->format('H:i:s'));
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function getCurrentMediaForPlaylist(Playlist $playlist)
    {
        return $this->getMediaForPlaylistBaseQuery($playlist)
            ->andWhere('pm.showFrom <= :nowPlusSecond')
            ->andWhere('pm.showTo > :nowPlusSecond')
            ->setParameter('nowPlusSecond', (new DateTime())->modify('+1 second')->format('H:i:s'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getFollowingMediaForPlaylist(Playlist $playlist, \DateTime $dateTime = new DateTime())
    {
        $qb = $this->createQueryBuilder('pm');
        $qb->andWhere('pm.showFrom >= :datetime')
        ->andWhere('pm.playlist = :playlist')
        ->setParameter('datetime', $dateTime->format('H:i:s'))
        ->setParameter('playlist', $playlist)
        ->orderBy('pm.showFrom', 'ASC');

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

//    /**
//     * @return PlaylistMedia[] Returns an array of PlaylistMedia objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PlaylistMedia
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
