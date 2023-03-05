<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Validator;

use Magento\Framework\Phrase;
use RunAsRoot\MessageQueueRetry\Exception\InvalidQueueConfigurationException;
use RunAsRoot\MessageQueueRetry\System\Config\MessageQueueRetryConfig as ConfigFieldNames;

class QueueConfigurationValidator
{
    /**
     * @throws InvalidQueueConfigurationException
     */
    public function validate(array $configValues): bool
    {
        $this->performUniqueValidation($configValues, ConfigFieldNames::MAIN_TOPIC_NAME, 'Main');
        $this->performUniqueValidation($configValues, ConfigFieldNames::DELAY_TOPIC_NAME, 'Delay');

        foreach ($configValues as $configValue) {
            $mainTopicName = $configValue[ConfigFieldNames::MAIN_TOPIC_NAME] ?? null;
            $delayTopicName = $configValue[ConfigFieldNames::DELAY_TOPIC_NAME] ?? null;

            if ($mainTopicName === null && $delayTopicName === null) {
                continue;
            }

            if ($mainTopicName === $delayTopicName) {
                throw new InvalidQueueConfigurationException(
                    new Phrase(
                        'The main topic name "%1" and delay topic name "%2" cannot be the same.',
                        [ $mainTopicName, $delayTopicName ]
                    )
                );
            }
        }

        return true;
    }

    /**
     * @throws InvalidQueueConfigurationException
     */
    public function performUniqueValidation(array $configValues, string $field, string $name): void
    {
        $topicNames = [];

        foreach ($configValues as $configValue) {
            if (!isset($configValue[$field])) {
                continue;
            }

            if (in_array($configValue[$field], $topicNames)) {
                throw new InvalidQueueConfigurationException(
                    new Phrase(
                        '%1 topic name "%2" is already used.',
                        [ $name, $configValue[$field] ]
                    )
                );
            }

            $topicNames[] = $configValue[$field];
        }
    }
}
