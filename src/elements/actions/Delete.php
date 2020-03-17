<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\base\ElementInterface;
use craft\db\Table;
use craft\elements\db\ElementQueryInterface;

/**
 * Delete represents a Delete element action.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class Delete extends ElementAction
{
    /**
     * @var bool Whether to permanently delete the elements.
     * @since 3.5.0
     */
    public $hard = false;

    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    /**
     * @inheritdoc
     * @since 3.5.0
     */
    public function getTriggerHtml()
    {
        if ($this->hard) {
            return '<div class="btn formsubmit">' . $this->getTriggerLabel() . '</div>';
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        if ($this->hard) {
            return Craft::t('app', 'Delete permanently');
        }

        return Craft::t('app', 'Delete');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        if ($this->confirmationMessage !== null) {
            return $this->confirmationMessage;
        }

        /** @var ElementInterface|string $elementType */
        $elementType = $this->elementType;

        if ($this->hard) {
            return Craft::t('app', 'Are you sure you want to permanently delete the selected {type}?', [
                'type' => $elementType::pluralLowerDisplayName(),
            ]);
        }

        return Craft::t('app', 'Are you sure you want to delete the selected {type}?', [
            'type' => $elementType::pluralLowerDisplayName(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        if ($this->hard) {
            $ids = $query->ids();
            if (!empty($ids)) {
                Craft::$app->getDb()->createCommand()
                    ->delete(Table::ELEMENTS, ['id' => $ids])
                    ->execute();
            }
        } else {
            $elementsService = Craft::$app->getElements();
            foreach ($query->all() as $element) {
                $elementsService->deleteElement($element);
            }
        }

        if ($this->successMessage !== null) {
            $this->setMessage($this->successMessage);
        } else {
            /** @var ElementInterface|string $elementType */
            $elementType = $this->elementType;
            $this->setMessage(Craft::t('app', '{type} deleted.', [
                'type' => $elementType::pluralDisplayName(),
            ]));
        }

        return true;
    }
}