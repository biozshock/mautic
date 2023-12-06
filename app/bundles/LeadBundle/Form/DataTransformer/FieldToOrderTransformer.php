<?php

namespace Mautic\LeadBundle\Form\DataTransformer;

use Mautic\LeadBundle\Entity\LeadField;
use Mautic\LeadBundle\Entity\LeadFieldRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<LeadField|null, int|null>
 */
class FieldToOrderTransformer implements DataTransformerInterface
{
    /**
     * @var LeadFieldRepository
     */
    private $leadFieldRepository;

    public function __construct(LeadFieldRepository $leadFieldRepository)
    {
        $this->leadFieldRepository = $leadFieldRepository;
    }

    /**
     * Transforms an object to an integer (order).
     *
     * @param int|null $order
     *
     * @return LeadField|null
     */
    public function transform($order)
    {
        if (!$order) {
            return null;
        }

        return $this->leadFieldRepository->findOneBy(['order' => $order]);
    }

    /**
     * Transforms a integer to an object.
     *
     * @param LeadField|null $field
     *
     * @return int|null
     */
    public function reverseTransform($field)
    {
        if (null === $field) {
            return null;
        }

        return $field->getOrder();
    }
}
