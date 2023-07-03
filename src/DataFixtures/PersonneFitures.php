<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Personne;
use App\Entity\Direction;
use App\Entity\Service;
use App\Entity\SousDirection;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PersonneFitures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
            $direction = [
                "Direction des Systèmes d'informations (DSI)",
                "Direction des Ressources Humaines (DRH)",
                "Direction des Affaires Financiers (DAF)",
                "Direction des Affaires Juridiques et du Contentieux",
                "Direction de la Planification des Statistiques et de l'évaluatio",
                "Direction de la Communication et des Relations Publiques (DCRP)",
                "Direction de la Qualité et de l'Accompagnement du Changement",
                "Direction des Concours (DC)",
                "Direction de la Qualité et de l'Accompagnement et du Changement (DQAC)",
                "Cabinet"
            ];

            $sousdir = [
                "S/D des Etude et Développement",
                "S/D du Contentieux",
                "S/D du Personnel",
                "S/D de l'Action sociale",
                "S/D Médicale"
            ];

            $service = [
                "Service web",
                "Sercvice Etude",
                "Service Intranet"
            ];
            
            for ($i=0; $i <100 ; $i++) { 
                $personne = new Personne();
                $dirDesign = new Direction();
                $dirDesign->setDesignation($faker->randomElement($direction));
                $personne->setDirection($dirDesign);

                $sousdirDesign = new SousDirection();
                $sousdirDesign->setDesignation($faker->randomElement($sousdir));
                $personne->setSousdirection($sousdirDesign);

                $serviceDesign = new Service();
                $serviceDesign->setDesignation($faker->randomElement($service));
                $personne->setService($serviceDesign);

                $personne->setMatricule(strtoupper($faker->bothify('######?')));
                $nom = $faker->lastName();
                $personne->setNom($nom);
                $personne->setPrenom($faker->firstName());
                $personne->setNomPere($nom.' '.$faker->firstNameMale());
                $personne->setNomMere($faker->lastName().' '.$faker->firstNameFemale());
                $personne->setSexe($faker->randomElement(['Masculin', 'Feminin'])); 
                $personne->setCivilite($faker->randomElement(['M', 'Mme']));
                $personne->setGrade($faker->randomElement(['C3', 'B3','A3','A4','A5','A6','A7']));
                $personne->setStructure("Ministère de la fonction publique");
                $personne->setLieuNaiss($faker->city());
                $personne->setEmploi($faker->jobTitle());
                $format = $faker->randomElement(['+225 05########', '+225 07########', '+225 01########']);
                $numeroTelephone = $faker->numerify($format);
                $personne->setTelephone($numeroTelephone);
                $startDate = '-60 years';
                $endDate = 'now';
                $dateBithDay = $faker->dateTimeBetween($startDate, $endDate);
                $personne->setDateNaiss($dateBithDay);
                $personne->setNbreEnfant($faker->numberBetween(0,20));
                $personne->setImage("bakuscv-64772a2b3bb1e.jpg");


                $manager->persist($personne);
        }
        $manager->flush();
    }
}
