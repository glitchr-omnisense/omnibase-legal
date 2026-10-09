<?php

namespace Base\Legal\Practice\Controller\Admin;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Context\AdminContext;
use Base\Admin\Menu\MenuBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * The back office's screens of the practice and of a profile: written by
 * the practice's administrator (ROLE_ADMIN), not only by the super-admin
 * omnibase/admin asks by default for anything that writes (openTo()); a
 * page of the bundle's own in the back office's chrome (page()).
 *
 * omnibase/office has the same two helpers (OpenToTrait, AdminPageTrait);
 * they are not used here because a jurist's site has no omnibase/office, and
 * a trait that is missing is a fatal error wherever the class is read - not
 * an exception a scan of the bundle's classes can step over.
 */
trait AdminScreenTrait
{
    private ?AdminContext $screenContext = null;
    private ?MenuBuilder $screenMenus = null;

    protected function openTo(Actions $actions, string $role = 'ROLE_ADMIN'): Actions
    {
        return $actions->setPermissions(array_fill_keys([
            Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE,
            Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_ADD_ANOTHER,
        ], $role));
    }

    #[Required]
    public function setScreenChrome(AdminContext $adminContext, MenuBuilder $menuBuilder): void
    {
        $this->screenContext = $adminContext;
        $this->screenMenus = $menuBuilder;
    }

    private function page(string $template, array $parameters = []): Response
    {
        if ([] === $this->screenContext->getMainMenu()) {
            $this->screenContext->setMainMenu($this->screenMenus->buildDefault());
        }
        if ([] === $this->screenContext->getUserMenu()) {
            $this->screenContext->setUserMenu($this->screenMenus->buildUserMenuDefault($this->getUser()));
        }

        return $this->render($template, ['admin_context' => $this->screenContext] + $parameters);
    }
}
