<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        \Lyrasoft\ActionLog\Module\Admin\ActionLog\ActionLogListView  The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\ActionLog\Module\Admin\ActionLog\ActionLogListView;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Form\Form;

/**
 * @var $form Form
 */

?>

<div x-title="toolbar" x-data="{ form: $store.grid.form, grid: $store.grid }" class="l-toolbar">
    {{-- Create --}}
    <a class="btn btn-success btn-sm uni-btn-export"
        href="{{ $nav->to('action_log_export') }}"
        style="min-width: 150px"
        target="_blank"
    >
        <i class="fa fa-file-excel"></i>
        @lang('action.log.toolbar.button.export')
    </a>
</div>
