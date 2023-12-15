<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        \Lyrasoft\ActionLog\Module\Admin\ActionLog\ActionLogListView The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\ActionLog\Module\Admin\ActionLog\ActionLogListView;
use Lyrasoft\ActionLog\Service\ActionLogService;
use Lyrasoft\Luna\Entity\User;
use Unicorn\Workflow\BasicStateWorkflow;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Http\Browser;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\ORM\ORM;

/**
 * @var $item ActionLog
 */

$workflow = $app->service(BasicStateWorkflow::class);

$orm = $app->retrieve(ORM::class);
$actionLogService = $app->retrieve(ActionLogService::class);
$browser = $app->retrieve(Browser::class);
?>

@extends('admin.global.body-list')

@section('toolbar-buttons')
    @include('list-toolbar')
@stop

@section('content')
    <form id="admin-form" action="" x-data="{ grid: $store.grid }"
        x-ref="gridForm"
        data-ordering="{{ $ordering }}"
        method="post">

        <x-filter-bar :form="$form" :open="$showFilters"></x-filter-bar>

        {{-- RESPONSIVE TABLE DESC --}}
        <div class="d-block d-lg-none mb-3">
            @lang('unicorn.grid.responsive.table.desc')
        </div>

        <div class="grid-table table-responsive-lg">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    {{-- Toggle --}}
                    <th style="width: 1%">
                        <x-toggle-all></x-toggle-all>
                    </th>

                    <th>
                        <x-sort field="action_log.time">
                            @lang('action.log.field.time')
                        </x-sort>
                        /
                        <x-sort field="action_log.id">
                            @lang('action.log.field.id')
                        </x-sort>
                    </th>

                    <th>
                        <x-sort field="action_log.ip">
                            @lang('action.log.field.ip')
                        </x-sort>
                    </th>

                    <th>
                        <x-sort field="action_log.user_id">
                            @lang('action.log.field.user')
                        </x-sort>
                    </th>

                    <th>
                        @lang('action.log.field.action')
                    </th>

                    <th>
                        @lang('action.log.field.entity')
                    </th>

                    <th>
                        @lang('action.log.field.ids')
                    </th>

                    <th>
                        @lang('action.log.field.device')
                    </th>
                </tr>
                </thead>

                <tbody>
                @forelse($items as $i => $item)
                        <?php
                        $user = $orm->toEntityOrNull(User::class, $item->user);
                        ?>
                    <tr>
                        {{-- Checkbox --}}
                        <td>
                            <x-row-checkbox :row="$i" :id="$item->getId()"></x-row-checkbox>
                        </td>

                        <td>
                            <div>
                                {{ $chronos->toLocalFormat($item->getTime(), 'Y-m-d H:i:s') }}
                            </div>
                            <div class="small">
                                ({{ $item->getId() }})
                            </div>
                        </td>

                        <td>
                            {{ $item->getIp() }}
                        </td>

                        <td>
                            <div>
                                {{ $user?->getName() ?? $item->getUsername() ?: $item->getEmail() }}
                            </div>
                            <div class="text-muted small">
                                {{ $user?->getUsername() ?: $item->getEmail() }}
                            </div>
                        </td>

                        <td>
                            {!! $actionLogService->formatTask($item) !!}
                        </td>

                        <td>
                            {!! $actionLogService->formatEntity($item) !!}
                        </td>

                        <td class="text-wrap">
                            {!! $item->getIds() !!}
                        </td>

                        <td>
                            @if ($item->getDevice())
                                {{ $item->getDevice() }}
                            @else
                                {{ $browser->device($item->getUa()) }}
                                ({{ $browser->platform($item->getUa()) }})
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="30">
                            <div class="c-grid-no-items text-center" style="padding: 125px 0;">
                                <h3 class="text-secondary">@lang('unicorn.grid.no.items')</h3>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div>
                <x-pagination :pagination="$pagination"></x-pagination>
            </div>
        </div>

        <div class="d-none">
            <input name="_method" type="hidden" value="PUT" />
            <x-csrf></x-csrf>
        </div>

        <x-batch-modal :form="$form" namespace="batch"></x-batch-modal>
    </form>

@stop
