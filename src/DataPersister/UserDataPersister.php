<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var DataPersisterInterface
     */
    private $decorated;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(DataPersisterInterface $decorated, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->decorated = $decorated;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function persist($data, array $context = [])
    {
        if ($data instanceof User && $data->getPlainPassword() !== null) {
            $data->setPassword(
                $this->passwordEncoder->encodePassword($data, $data->getPlainPassword())
            );
        }

        $this->decorated->persist($data);
    }

    public function remove($data, array $context = [])
    {
        $this->decorated->remove($data);
    }
}