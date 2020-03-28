<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Locales;

final class LocaleConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return LocaleField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field->setFormTypeOptionIfNotSet('attr.data-widget', 'select2');

        if (null === $localeCode = $field->getValue()) {
            return;
        }

        $localeName = $this->getLocaleName($localeCode);
        if (null === $localeName) {
            throw new \InvalidArgumentException(sprintf('The "%s" value used as the locale code of the "%s" field is not a valid ICU locale code.', $localeCode, $field->getName()));
        }

        $field->setFormattedValue($localeName);
    }

    private function getLocaleName(string $localeCode): ?string
    {
        // Compatibility with Symfony versions before 4.3
        if (!class_exists(Locales::class)) {
            return Intl::getLocaleBundle()->getLocaleName($localeCode);
        }

        try {
            return Locales::getName($localeCode);
        } catch (MissingResourceException $e) {
            return null;
        }
    }
}