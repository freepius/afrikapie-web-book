<?php

namespace App\Model\Factory;

use Freepius\Model\EntityFactory;
use Symfony\Component\Validator\Constraints as Assert;

class Contact extends EntityFactory
{
    /* @{inheritdoc} */
    public function instantiate()
    {
        return [
            'name'    => '',
            'email'   => '',
            'subject' => '',
            'message' => '',
        ];
    }

    /* @{inheritdoc} */
    protected function processInputData(array $data)
    {
        return [
            'name'    => trim($data['name']),
            'email'   => trim($data['email']),
            'subject' => trim($data['subject']),
            'message' => trim(strip_tags($data['message'])),
        ];
    }

    /**
     * @{inheritdoc}
     */
    protected function getConstraints(array $entity)
    {
        return new Assert\Collection([
            'name'    => new Assert\NotBlank(),
            'email'   => new Assert\Email(),
            'subject' => new Assert\NotBlank(),
            'message' => new Assert\NotBlank(),
        ]);
    }
}
