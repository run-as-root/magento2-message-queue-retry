<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use RunAsRoot\MessageQueueRetry\Exception\InvalidQueueConfigurationException;
use RunAsRoot\MessageQueueRetry\Validator\QueueConfigurationValidator;

class QueuesConfig extends ArraySerialized
{
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private QueueConfigurationValidator $queueConfigurationValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * @throws InvalidQueueConfigurationException
     */
    public function beforeSave(): self
    {
        $value = $this->getValue();

        if (!is_array($value)) {
            return parent::beforeSave();
        }

        $this->queueConfigurationValidator->validate($value);

        return parent::beforeSave();
    }
}
