<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CreateMediaObjectAction extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): MediaObject
    {

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        try {
            $mediaObject = new MediaObject();
            $mediaObject->file = $uploadedFile;
            $mediaObject->setCreatedAt(new DateTimeImmutable("now"));
            $mediaObject->setSource($request->request->get('source'));
            return $mediaObject;
        } catch (ORMException $e) {
            throw new BadRequestHttpException('[ERROR 1]',$e);
        }

    }

}
