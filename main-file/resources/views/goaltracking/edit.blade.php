{{Form::model($goalTracking,array('route' => array('goaltracking.update', $goalTracking->id), 'method' => 'PUT', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['goal tracking']) }}"
         data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>

        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('branch',__('Branch'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::select('branch',$brances,null,array('class'=>'form-control select','required'=>'required'))}}
                <div class="text-xs mt-1">
                    {{ __('Create branch here.') }} <a href="{{ route('branch.index') }}"><b>{{ __('Create branch') }}</b></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('goal_type',__('GoalTypes'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::select('goal_type',$goalTypes,null,array('class'=>'form-control select','required'=>'required'))}}
                <div class="text-xs mt-1">
                    {{ __('Create goal type here.') }} <a href="{{ route('goaltype.index') }}"><b>{{ __('Create goal type') }}</b></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('start_date',__('Start Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('start_date',null,array('class' => 'form-control ','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('end_date',__('End Date'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::date('end_date',null,array('class' => 'form-control ','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('subject',__('Subject'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('subject',null,array('class'=>'form-control ','required'=>'required', 'placeholder'=>__('Enter Subject')))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('target_achievement',__('Target Achievement'),['class'=>'form-label'])}}
                {{Form::text('target_achievement',null,array('class'=>'form-control', 'placeholder'=>__('Enter Target Achievement')))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('description',__('Description'),['class'=>'form-label'])}}
                {{Form::textarea('description',null,array('class'=>'form-control', 'placeholder'=>__('Enter Description')))}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('status',__('Status'),['class'=>'form-label'])}}
                {{Form::select('status',$status,null,array('class'=>'form-control select'))}}
            </div>
        </div>
        <div class="col-md-12">
            <fieldset id='demo1' class="rating">
                <input class="stars" type="radio" id="rating-5" name="rating" value="5" {{($goalTracking->rating==5) ? 'checked':''}} >
                <label class="full" for="rating-5" title="Awesome - 5 stars"></label>
                <input class="stars" type="radio" id="rating-4" name="rating" value="4" {{($goalTracking->rating==4) ? 'checked':''}}>
                <label class="full" for="rating-4" title="Pretty good - 4 stars"></label>
                <input class="stars" type="radio" id="rating-3" name="rating" value="3" {{($goalTracking->rating==3) ? 'checked':''}}>
                <label class="full" for="rating-3" title="Meh - 3 stars"></label>
                <input class="stars" type="radio" id="rating-2" name="rating" value="2" {{($goalTracking->rating==2) ? 'checked':''}}>
                <label class="full" for="rating-2" title="Kinda bad - 2 stars"></label>
                <input class="stars" type="radio" id="technical-1" name="rating" value="1" {{($goalTracking->rating==1) ? 'checked':''}}>
                <label class="full" for="technical-1" title="Sucks big time - 1 star"></label>
            </fieldset>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <input type="range" class="slider w-100 mb-0 " name="progress" id="myRange" value="{{$goalTracking->progress}}" min="1" max="100" oninput="ageOutputId.value = myRange.value">
                <output name="ageOutputName" id="ageOutputId">{{$goalTracking->progress}}</output>
                %
            </div>
        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
