<?php

namespace App\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use App\Entity\Photo;

class CreatePhotoEntityBeforeFileUpload implements EventSubscriberInterface
{
    public function onRegisterEmailConfirm(GenericEvent $event)
    {
//        $entity = $event->getSubject();
////        $entity->name = $event->getArgument("request")->files->parameters["photo"]["pathFile"]->originalName;
//        var_dump($event->getArguments());
//
//        $event['entity'] = $entity;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_PERSIST => 'onRegisterEmailConfirm'
        ];
    }
}