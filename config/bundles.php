<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Nelmio\ApiDocBundle\NelmioApiDocBundle::class => ['all' => true],
    FOS\RestBundle\FOSRestBundle::class => ['all' => true],
    Sonata\EasyExtendsBundle\SonataEasyExtendsBundle::class => ['all' => true],
    Sonata\DatagridBundle\SonataDatagridBundle::class => ['all' => true],
    Sonata\CoreBundle\SonataCoreBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],

    Magenta\Bundle\SWarrantyModelBundle\MagentaSWarrantyModelBundle::class => ['all' => true, 'test' => true],
    Magenta\Bundle\SWarrantyMediaApiBundle\MagentaSWarrantyMediaApiBundle::class => ['all' => true, 'test' => true],
    Magenta\Bundle\SWarrantyJWTBundle\MagentaSWarrantyJWTBundle::class => ['all' => true, 'test' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
];
