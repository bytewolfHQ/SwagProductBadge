<?php
declare(strict_types=1);

namespace Swag\ProductBadge;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class SwagProductBadge extends Plugin
{
    private const string CUSTOM_FIELD_SET_NAME = 'swag_product_badge';

    public function install(InstallContext $installContext): void
    {
        $this->createCustomFields($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $this->removeCustomFields($uninstallContext->getContext());
    }

    private function createCustomFields(Context $context): void
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $customFieldSetRepository->create([
            [
                'name' => self::CUSTOM_FIELD_SET_NAME,
                'config' => [
                    'label' => [
                        'en-GB' => 'Produkt-Badge',
                        'de-DE' => 'Product Badge',
                    ],
                    'relations' => [['entityName' => 'product']],
                    'customFields' => [
                        [
                            'name' => 'swag_badge_label',
                            'type' => CustomFieldTypes::TEXT,
                            'config' => [
                                'label' => [
                                    'de-DE' => 'Badge-Text (z.B. "Neu", "Bestseller")',
                                    'en-GB' => 'Badge text (e.g. "New", "Bestseller")',
                                ],
                                'componentName' => 'sw-field',
                                'customFieldType' => 'text',
                            ]
                        ],
                        [
                            'name' => 'swag_badge_color',
                            'type' => CustomFieldTypes::SELECT,
                            'config' => [
                                'label' => [
                                    'de-DE' => 'Badge-Farbe',
                                    'en-GB' => 'Badge color',
                                ],
                                'options' => [
                                    ['value' => 'primary',   'label' => ['de-DE' => 'Blau',  'en-GB' => 'Blue']],
                                    ['value' => 'success',   'label' => ['de-DE' => 'Grün',  'en-GB' => 'Green']],
                                    ['value' => 'danger',    'label' => ['de-DE' => 'Rot',   'en-GB' => 'Red']],
                                    ['value' => 'warning',   'label' => ['de-DE' => 'Gelb',  'en-GB' => 'Yellow']],
                                    ['value' => 'secondary', 'label' => ['de-DE' => 'Grau',  'en-GB' => 'Grey']],
                                ],
                                'componentName' => 'sw-single-select',
                                'customFieldType' => 'select',
                            ],
                        ],
                    ]
                ]
            ]
        ], $context);
    }

    private function removeCustomFields(Context $context): void
    {
        /** @var EntityRepository $customFieldSetRepo */
        $customFieldSetRepo = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME));

        $result = $customFieldSetRepo->searchIds($criteria, $context);

        if ($result->getTotal() === 0) {
            return;
        }

        $ids = array_map(fn (string $id) => ['id' => $id], $result->getIds());
        $customFieldSetRepo->delete($ids, $context);
    }
}
