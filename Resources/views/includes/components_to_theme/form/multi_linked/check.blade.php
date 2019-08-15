@php
	
	$model=Form::getModel();
    $val=$model->$name;
    //$all=$model->{'all_'.$name};
    $model_linked=Theme::xotModel(Str::singular($name));
    $_panel=Theme::panelModel($model_linked);
    $all=$model_linked->get();
    
    ddd($all);
@endphp