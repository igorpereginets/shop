<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserIconController
{

    public function addIcon(User $data, Request $request)
    {
        $uploadedFile = $request->files->get('iconFile');

        $data->setIconFile($uploadedFile);

        return $data;
    }
}