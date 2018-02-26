@extends('layouts.master')
@section('title',__('user.user management'))
@section('content')
<?php
use App\Helpers\General;
?>
    <div class="row">
        <div class="col-md-12 ">
            <div class="section__top-menu">
                <ul class="button-group">
                    @if ($userLogged->isRoleSR() || $userLogged->isRoleAdmin())
                        <li>
                            <a href="{!!URL::route('user.register')!!}" class="btn btn-primary">{!!__('core.Signup')!!}</a>
                        </li>
                        <li>
                            <a href="{!!URL::route('user.upload')!!}" class="btn btn-primary">{!!__('core.CSV upload')!!}</a>
                        </li>
                        <li>
                            <button class="btn btn-primary" data-btn-submit="ajax"
                                action="{!! route('user.csv.export') !!}"
                                data-cb-success="userExport">
                                <i class="fa fa-download loading-hidden-submit"></i>
                                <i class="fa fa-spinner fa-spin hidden loading-submit"></i>
                                {!!__('core.CSV download')!!}
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="col-md-12 ">
            <div class="section__filter">
                <h4 class="title__section--primary">{!!__('core.Search condition')!!}</h4>
                <div class="row">
                    <div class="col-xs-12">
                        <form method="get" class="form--search-user form-label-weight-normal row" action="{!!route('user.search')!!}"
                            autocomplete="off" data-s-dom="container">
                            <div class="col-md-6">
                                <div class="row d-flex align-items-center mt-4">
                                    <div class="col-xs-3 text-right">
                                        <label for="search-sr">{!!__('user.SR name')!!}</label>
                                    </div>
                                    <div class="col-xs-9 col-md-4">
                                        <select class="form-control" data-s-input="s-sr">
                                            <option value="">{{ __('user.Please select') }}</option>
                                                @foreach ($showrooms as $key => $value)
                                                    <option value="{!! $key !!}">{{ $value['sr_name'] }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row d-flex align-items-center mt-4">
                                    <div class="col-xs-3 text-right">
                                        <label for="search-sr">{!!__('user.User name')!!}</label>
                                    </div>
                                    <div class="col-xs-9 col-md-4">
                                        <input type="text" class="form-control" id="username" data-s-input="s-name" />
                                    </div>
                                </div>

                                <div class="row d-flex align-items-center mt-4">
                                    <div class="col-xs-3 text-right">
                                        <label for="search-sr">{!!__('user.Login ID')!!}</label>
                                    </div>
                                    <div class="col-xs-9 col-md-4">
                                        <input type="text" class="form-control" id="login-id" data-s-input="s-login_id" />
                                    </div>
                                </div>


                                <div class="row d-flex align-items-center mt-4">
                                    <div class="col-xs-3 text-right">
                                        <label for="search-sr">{!!__('core.State')!!}</label>
                                    </div>
                                    <div class="col-xs-9 col-md-4">
                                        <div class="checkbox-wrap">
                                            <input type="radio" value="1" data-s-input="s-status"
                                                id="search-active" name="s-enable" />
                                            <label for="search-active" class="font-weight-normal margin-right-20">{{ __('core.Active') }}</label>
                                            <input type="radio"  value="0" data-s-input="s-status"
                                                id="search-disable" name="s-enable" />
                                            <label for="search-disable" class="font-weight-normal">{{ __('core.Disabled') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form__submit">
                                    <button type="button" class="btn btn-primary" data-s-dom="exec">{!!__('core.Search')!!}</button>
                                    <a href="#" class="btn--reset" data-s-dom="reset">{!!__('core.Reset search')!!}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 ">
            <div class="section__result" data-pager="container" data-pager-url="{!!route('user.list.ajax')!!}">
                <!-- Result not found -->
                <h4 class="text-danger text-center hidden" data-pager-item="no-result">検索した結果該当するファイルがありませんでした。</h4>
                <div data-pager-item="results" class="hidden">
                    <!-- End result not found -->
                    <h4 class="title__section--primary">{!!__('core.Search result')!!}</h4>
                    <div class="result__wrapper">
                        <div class="result__top">
                            <div data-pager-flag="flag-info"></div>
                            <div data-pager-flag="flag-pager"></div>
                        </div>
                        <div class="result__record table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="10%" scope="col">SR</th>
                                        <th width="25%" scope="col">{!!__('user.User name')!!}</th>
                                        <th width="25%" scope="col">{!!__('user.Login ID')!!}</th>
                                        <th width="20%" scope="col">{!!__('user.Jurisdiction')!!}</th>
                                        <th width="10%" scope="col">{!!__('user.Valid')!!}</th>
                                        <th width="10%" scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody data-pager-item="collection">
                                    <tr>
                                        <td data-pager-col="sr_name"></td>
                                        <td data-pager-col="user_name"></td>
                                        <td data-pager-col="login_id"></td>
                                        <td data-pager-col="role_name"></td>
                                        <td data-pager-col="enable_label"></td>
                                        <td data-pager-col="col-action">
                                            <a href="{!!route('user.detail', ['id' => '0'])!!}"
                                               class="btn btn-primary" data-pager-flag="col-href-id">{!!__('core.Details')!!}</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="result__bottom">
                            <div data-pager-flag="flag-pager"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="hidden">
    @include('partial.pager-page')
    @include('partial.pager-info')
</div>
@endsection
@section('script')
<script>
<?php
$trans = __('user');
$trans['Display order'] = __('core.Display order');
?>
    var UserGlob = {
       activeLabel: {!!json_encode($activeLabel)!!},
       roles: {!!json_encode($roles)!!},
       collectionPager: {!!$collectionPager->toJson()!!},
       trans: {!!json_encode($trans)!!}
    };
    var importCSVUrl = '{{ route("user.csv.import") }}';
</script>
<script type="text/javascript" src="{!! General::asset('js/user/user_list.js') !!}"></script>
<script type="text/javascript" src="{{ asset('js/csv-helper/encoding.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/csv-helper/FileSaver.min.js') }}"></script>
@endsection
