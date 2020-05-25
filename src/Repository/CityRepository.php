<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Method in order to find the cities whose names start with at least 2 characters
     * @param firstLetters : the characters used for the research
     */
    public function getCitiesStartingWith(string $firstLetters){
        //if the string is empty display all city
        if ($firstLetters == "."){
            $cities = $this->findAll();
        }else{
            //create the querry for finding the 10 matching cities
            $cities = $this->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', $firstLetters.'%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        }
        return $cities;
    }

    /**
     * Method in order to find the nearest city geographically
     * @param city : the city whose neighbor we have to find
     */
    public function getNearestCities(City $city){
        //Create and execute the request in order to find the nearest city
        # 0.07 on each coords means a 11 km wide radius around the targeted city
        return $this->createQueryBuilder('c')
        ->where("ABS(c.latitude - :latitude) < 0.07")
        ->andWhere("ABS(c.longitude - :longitude) < 0.07")
        ->setParameter('latitude', $city->getLatitude())
        ->setParameter('longitude', $city->getLongitude())
        ->getQuery()
        ->getResult();
    }
}

