<?php

    namespace KarimQaderi\Zoroaster\Http\Controllers\Resource;

    use App\Http\Controllers\Controller;
    use KarimQaderi\Zoroaster\Http\Requests\RequestField;
    use KarimQaderi\Zoroaster\Http\Requests\ResourceRequest;

    class ResourceDestroyController extends Controller
    {
        use \KarimQaderi\Zoroaster\Fields\Traits\Validator;

        public function handle(ResourceRequest $request)
        {
            foreach(request()->resourceId as $id){

                /**
                 * نظر مورد رکورد کردن پیدا
                 */
                $find = $request->getModelAndWhereTrashed()->where([$request->newModel()->getKeyName() => $id])->first();


                /**
                 * دسترسی سطع بررسی
                 */
                if($request->Resource()->authorizedToForceDelete($find)){

                    /**
                     * ها فیلد به حذف درخواست ارسال
                     * بشن حذف هم فیلد اون های عکس باید شه می حذف Resource رکورد وقتی بگیرید نظر در رو عکس فیلد یه مثال برای
                     */
                    $this->ResourceDestroyField($request , $find);

                    if($request->isForceDeleting())
                        $find->forceDelete();
                    else
                        $request->newModel()->destroy($id);
                }

            }


            if(request()->has('redirect'))
                redirect(request()->redirect)->with(['success' => 'منبع مورد نظر حذف شد' ])->send();

            return response(['status' => 'ok']);
        }

        private function ResourceDestroyField(ResourceRequest $request , $find)
        {
            $customResourceController = $request->ResourceFields(function($field){ return true; });

            $ResourceError = [];
            foreach($customResourceController as $field){

                $RequestField = new RequestField();
                $RequestField->request = $request->Request();
                $RequestField->resource = $find;
                $RequestField->field = $field;
                $RequestField->fieldAll = $customResourceController;
                $RequestField->MergeResourceFieldsAndRequest = null;

                $ResourceDestroy = (object)$field->ResourceDestroy($RequestField);
                if(isset($ResourceDestroy->error) && $ResourceDestroy->error !== null){

                    if(is_array($ResourceDestroy->error))
                        $ResourceError = array_merge($ResourceError , $ResourceDestroy->error);
                    else
                        $ResourceError = array_merge($ResourceError , $ResourceDestroy->error->messages());

                }
            }


            if(count($ResourceError) !== 0)
                $this->SendErrors($ResourceError);
        }

    }