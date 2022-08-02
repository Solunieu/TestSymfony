<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FavorisRepository::class)
 */
class Favoris
{
    /**
	 * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_utilisateur;

    /**
	 * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id_film;

    public function getIdUtilisateur(): ?int
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(int $id_utilisateur): self
    {
        $this->id_utilisateur = $id_utilisateur;

        return $this;
    }

    public function getIdFilm(): ?int
    {
        return $this->id_film;
    }

    public function setIdFilm(int $id_film): self
    {
        $this->id_film = $id_film;

        return $this;
    }
}
