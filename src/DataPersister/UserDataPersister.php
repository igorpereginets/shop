<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements DataPersisterInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var DataPersisterInterface
     */
    private $decorated;

    public function __construct(DataPersisterInterface $decorated, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->decorated = $decorated;
    }

    public function supports($data): bool
    {
        return $this->decorated->supports($data);
    }

    public function persist($data)
    {
        if ($data instanceof User && $data->getPlainPassword() !== null) {
            $data->setPassword(
                $this->passwordEncoder->encodePassword($data, $data->getPlainPassword())
            );
        }

        return $this->decorated->persist($data);
    }

    public function remove($data)
    {
        $this->decorated->remove($data);
    }
}