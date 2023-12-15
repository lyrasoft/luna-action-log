<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Module\Admin\ActionLog;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\ActionLog\Module\Admin\ActionLog\Form\EditForm;
use Lyrasoft\ActionLog\Repository\ActionLogRepository;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewMetadata;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Form\FormFactory;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\ORM\ORM;

/**
 * The ActionLogEditView class.
 */
#[ViewModel(
    layout: 'action-log-edit',
    js: 'action-log-edit.js'
)]
class ActionLogEditView implements ViewModelInterface
{
    use TranslatorTrait;

    public function __construct(
        protected ORM $orm,
        protected FormFactory $formFactory,
        protected Navigator $nav,
        #[Autowire] protected ActionLogRepository $repository
    ) {
    }

    /**
     * Prepare
     *
     * @param  AppContext  $app
     * @param  View        $view
     *
     * @return  mixed
     */
    public function prepare(AppContext $app, View $view): mixed
    {
        $id = $app->input('id');

        /** @var ActionLog $item */
        $item = $this->repository->getItem($id);

        // Bind item for injection
        $view[ActionLog::class] = $item;

        $form = $this->formFactory
            ->create(EditForm::class)
            ->fill(
                $this->repository->getState()->getAndForget('edit.data')
                    ?: $this->orm->extractEntity($item)
            );

        return compact('form', 'id', 'item');
    }

    #[ViewMetadata]
    protected function prepareMetadata(HtmlFrame $htmlFrame): void
    {
        $htmlFrame->setTitle(
            $this->trans('unicorn.title.edit', title: 'ActionLog')
        );
    }
}
