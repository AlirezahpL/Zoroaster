<?php

    namespace KarimQaderi\Zoroaster\Http\Controllers\Resource;

    use App\Http\Controllers\Controller;
    use KarimQaderi\Zoroaster\Http\Requests\ResourceRequest;
    use KarimQaderi\Zoroaster\Traits\ResourceIndexQuery;

    class ResourceIndexController extends Controller
    {
        use ResourceIndexQuery;

        public function handle(ResourceRequest $ResourceRequest)
        {

            /**
             * دسترسی سطع بررسی
             */
            $ResourceRequest->Resource()->authorizeToIndex($ResourceRequest->newModel());

            if(!request()->ajax())
                return view('Zoroaster::resources.index')->with(['resource' => $ResourceRequest->Resource()]);

            /**
             * فیلترها اعمال
             */
            $resources = $this->toQuery($ResourceRequest->newModel() , $ResourceRequest);
            $render = null;
            $render .= view('Zoroaster::resources.index-resource')->with([
                'ResourceRequest' => $ResourceRequest ,
                'resourceClass' => $ResourceRequest->Resource() ,
                'model' => $ResourceRequest->newModel() ,
                'resources' => $resources ,
                'fields' =>
                    $ResourceRequest->ResourceFields(function($field)
                    {
                        if($field !== null && $field->showOnIndex == true)
                            return true;
                        else
                            return false;
                    }) ,
            ]);

            return response()->json([
                'render' => \Zoroaster::minifyHtml($render) ,
                'resource' => $ResourceRequest->resourceClass ,
                'status' => 'ok' ,
            ]);


        }


    }