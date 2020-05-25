<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\EquatableInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("login", message="The login is already used")
 * @UniqueEntity("mail", message="The mail is already in use on this website")
 */
class User implements UserInterface, \Serializable, EquatableInterface
{

    const LOCALES = ['en', 'fr'];
    const THEMES = ['light', 'dark'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(
     *          min=3,
     *          minMessage="Please a login longer than 2 characters",
     *          max=50,
     *          maxMessage="Your login can not exceed 50 characters",
     * )
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Please a login longer than 5 characters")
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

     /**
      *@ORM\Column(type="string", length=50)
      *@Assert\Length(
      *    min=2,
      *    max=50,
      *    maxMessage="Your surname on the website can not exceed 50 characters",
      *)
      */
    private $surname;

    /**
     * @Assert\NotBlank(message = "The mail is not a valid mail.")
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(
     *     message = "The mail '{{ value }}' is not a valid mail."
     * )
     */
    private $mail;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default" : 0.0})
     * Assert\Range(min=0, max=600)
     */
    private $balance = 0;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice(choices=User::LOCALES, message="Choose a valid locale.")
     * @Assert\Locale(
     *     canonicalize = true
     *)
     *@Assert\NotBlank
      */
    private $locale = "en";


    /**
      * @Assert\Choice(choices=User::THEMES, message="Choose a valid themes.")
      * @ORM\Column(type="string", length=25)
      */
    private $theme = "light";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function serialize(){
        return \serialize([
            $this->id,
            $this->login,
            $this->password,
            $this->locale,
            $this->theme,
        ]);
    }

    public function unserialize($serialized){
        list(
            $this->id,
            $this->login,
            $this->password,
            $this->locale,
            $this->theme,
        ) = \unserialize($serialized, ['allowed_class' => false]);
    }

    public function __toString(){
        return $this->login.' '.$this->mail;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($user->getLocale() != $this->locale || $user->getTheme() != $this->theme )
        {
            dump("need refresh");
            return false;
        }
        return true;
    }

    public function getTheme(): ?string
    {
      return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }
}