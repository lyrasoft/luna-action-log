<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Module\Admin\ActionLog\Form;

use Lyrasoft\Luna\Field\UserModalField;
use Unicorn\Enum\BasicState;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Form\Attributes\FormDefine;
use Windwalker\Form\Attributes\NS;
use Windwalker\Form\Field\ListField;
use Windwalker\Form\Field\SearchField;
use Windwalker\Form\Form;

class GridForm
{
    use TranslatorTrait;

    #[FormDefine]
    #[NS('search')]
    public function search(Form $form): void
    {
        $form->add('*', SearchField::class)
            ->label($this->trans('unicorn.grid.search.label'))
            ->placeholder($this->trans('unicorn.grid.search.label'))
            ->onchange('this.form.submit()');
    }

    #[FormDefine]
    #[NS('filter')]
    public function filter(Form $form): void
    {
        $form->add('action_log.user_id', UserModalField::class)
            ->label($this->trans('action.log.field.user'))
            ->buttonText('<i class="far fa-user"></i>')
            ->onchange('this.form.submit()');

        $form->add('action_log.method', ListField::class)
            ->label($this->trans('action.log.field.method'))
            ->option($this->trans('unicorn.select.placeholder'), '')
            ->option('GET', 'GET')
            ->option('POST', 'POST')
            ->option('PUT', 'PUT')
            ->option('DELETE', 'DELETE')
            ->option('PATCH', 'PATCH')
            ->option('HEAD', 'HEAD')
            ->option('OPTIONS', 'OPTIONS')
            ->onchange('this.form.submit()');
    }

    #[FormDefine]
    #[NS('batch')]
    public function batch(Form $form): void
    {
        $form->add('state', ListField::class)
            ->label($this->trans('unicorn.field.state'))
            ->option($this->trans('unicorn.select.no.change'), '')
            ->registerFromEnums(BasicState::class, $this->lang);
    }
}
