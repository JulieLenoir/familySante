<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProfessionalRepository;

use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProfessionalRepository::class)]
#[UniqueEntity(
    fields: ['name', 'speciality', 'contactUrl', 'city'],
    message: 'Ce professionnel existe déjà dans la base de données.'
)]


class Professional
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['professional:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $speciality = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $bookingLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $city = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?int $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['professional:read', 'professional:write'])]
    private ?string $additionalInformation = null;

    #[ORM\OneToMany(mappedBy: 'professional', targetEntity: Appointment::class)]
    #[Groups(['professional:read'])]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
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

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function setSpeciality(string $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getBookingLink(): ?string
    {
        return $this->bookingLink;
    }

    public function setBookingLink(?string $bookingLink): static
    {
        $this->bookingLink = $bookingLink;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(?int $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    public function setAdditionalInformation(?string $additionalInformation): static
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setProfessional($this);
        }
        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            if ($appointment->getProfessional() === $this) {
                $appointment->setProfessional(null);
            }
        }
        return $this;
    }
}
