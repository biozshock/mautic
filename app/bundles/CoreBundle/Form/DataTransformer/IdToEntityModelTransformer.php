<?php

namespace Mautic\CoreBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<array<mixed>|int|string, array<mixed>|int|string>
 */
class IdToEntityModelTransformer implements DataTransformerInterface
{
    private EntityManagerInterface|EntityManager $em;

    /**
     * @var class-string
     */
    private string $repository;

    private string $id;

    private bool $isArray;

    /**
     * @param class-string $repo
     */
    public function __construct(EntityManagerInterface $em, string $repo, string $identifier = 'id', bool $isArray = false)
    {
        $this->em         = $em;
        $this->repository = $repo;
        $this->id         = $identifier;
        $this->isArray    = $isArray;
    }

    /**
     * @param array<mixed>|object|null $entity
     * @return array<mixed>|int|string
     */
    public function transform($entity)
    {
        $func = 'get'.ucfirst($this->id);

        if (!$this->isArray) {
            if (is_null($entity) || !is_object($entity) || !method_exists($entity, $func)) {
                return '';
            }

            return $entity->$func();
        }

        if (is_null($entity) && !is_array($entity) && !$entity instanceof PersistentCollection) {
            return [];
        }

        $return = [];
        foreach ($entity as $e) {
            $return[] = $e->$func();
        }

        return $return;
    }

    /**
     * @param array<mixed>|int|string $id
     * @return array<mixed>|object|null
     * @throws TransformationFailedException if object is not found
     */
    public function reverseTransform($id)
    {
        if (!$this->isArray) {
            if (!$id) {
                return null;
            }

            $entity = $this->em
                ->getRepository($this->repository)
                ->findOneBy([$this->id => $id]);

            if (null === $entity) {
                throw new TransformationFailedException(sprintf('An entity with a/an '.$this->id.' of "%s" does not exist!', $id));
            }

            return $entity;
        }

        if (empty($id) || !is_array($id)) {
            return [];
        }

        $repo   = $this->em->getRepository($this->repository);
        $prefix = $repo->getTableAlias();

        $entities = $repo->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => $prefix.'.'.$this->id,
                        'expr'   => 'in',
                        'value'  => $id,
                    ],
                ],
            ],
            'ignore_paginator' => true,
        ]);

        if (!count($entities)) {
            throw new TransformationFailedException(sprintf('Entities with a/an '.$this->id.' of "%s" does not exist!', $id));
        }

        return $entities;
    }

    /**
     * Set the repository to use.
     *
     * @param string $repo
     */
    public function setRepository($repo): void
    {
        $this->repository = $repo;
    }

    /**
     * Set the identifier to use.
     *
     * @param string $id
     */
    public function setIdentifier($id): void
    {
        $this->id = $id;
    }
}
