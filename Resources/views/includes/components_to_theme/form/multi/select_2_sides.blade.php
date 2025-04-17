@php
    /**
     * https://github.com/crlcu/multiselect 
     * bower install multiselect-two-sides
     */
    //ddd(\func_get_args());
    //ddd($this);
    //ddd($this->getCompiler()->getPath()); //questo mi restituisce il path
	//extend::includes.components.form.multi.select_2_sides
    //Theme::addScript('/theme/bc/multiselect/dist/js/multiselect.js'); 
    $t=$comp_ns.'/dist/js/field.js';
    Theme::test($t);
    Theme::add($comp_ns.'/dist/js/field.js');
    //Theme::add('extend::includes.components.form.multi/dist/css/field.css'); //vuoto 
    //Theme::add('/home/vagrant/code/htdocs/lara/foodm/Modules/Extend/Services/../Resources/views/includes/components/form/multi')
    $model=Form::getModel();
    $val=$model->$name;
    //$all=$model->{'all_'.$name};
    $model_linked=Theme::xotModel(Str::singular($name));
    $_panel=Theme::panelModel($model_linked);
    $all=$model_linked->get();
   //ddd($_panel);
@endphp

<br style="clear:both"/>
  <p>{{ trans('lu::help.nota_multiselect') }}</p><br/>
<div class="row">
  <br style="clear:both"/>
    <div class="col-sm-5">
        <select name="{{$name}}[from][]" id="multiselect" class="form-control" size="8" multiple="multiple">
            @foreach($all as $k => $v)
            {{--
            <option value="{{ $v->opt_key }}" >{{ $v->opt_label }}</option>
            --}}
            <option value="{{ $_panel->optionId($v) }}" >{{ $_panel->optionLabel($v) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-sm-2">
        <button type="button" id="multiselect_rightAll" class="btn btn-block">
            <i class="fas fa-angle-double-right"></i>
        </button>
        <button type="button" id="multiselect_rightSelected" class="btn btn-block">
            <i class="fas fa-chevron-right"></i>
        </button>
        <button type="button" id="multiselect_leftSelected" class="btn btn-block">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" id="multiselect_leftAll" class="btn btn-block">
             <i class="fas fa-angle-double-left"></i>
        </button>
    </div>
      
    <div class="col-sm-5">
        <select name="{{$name}}[to][]" id="multiselect_to" class="form-control" size="8" multiple="multiple">
            @foreach($val as $k => $v)
            {{--
            <option value="{{ $v->opt_key }}" >{{ $v->opt_label }}</option>
            --}}
            <option value="{{ $_panel->optionId($v) }}" >{{ $_panel->optionLabel($v) }}</option>
            @endforeach
        </select>
    </div>
    
</div>
{{--
@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#multiselect').multiselect();
});
</script>
@endpush
--}}