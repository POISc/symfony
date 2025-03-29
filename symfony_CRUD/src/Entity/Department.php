<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\Unique]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'department')]
    private Collection $foreignKey;

    public function __construct()
    {
        $this->foreignKey = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getForeignKey(): Collection
    {
        return $this->foreignKey;
    }

    public function addForeignKey(User $foreignKey): static
    {
        if (!$this->foreignKey->contains($foreignKey)) {
            $this->foreignKey->add($foreignKey);
            $foreignKey->setDepartment($this);
        }

        return $this;
    }

    public function removeForeignKey(User $foreignKey): static
    {
        if ($this->foreignKey->removeElement($foreignKey)) {
            // set the owning side to null (unless already changed)
            if ($foreignKey->getDepartment() === $this) {
                $foreignKey->setDepartment(null);
            }
        }

        return $this;
    }
}
