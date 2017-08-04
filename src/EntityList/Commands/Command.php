<?php

namespace Code16\Sharp\EntityList\Commands;

use Code16\Sharp\Form\Fields\SharpFormField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Base class for Commands. Handle returns (info, refresh, reload...),
 * form creation, and validation.
 *
 * Class Command
 * @package Code16\Sharp\EntityList\Commands
 */
abstract class Command
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param string $message
     * @return array
     */
    protected function info(string $message)
    {
        return [
            "action" => "info",
            "message" => $message
        ];
    }

    /**
     * @return array
     */
    protected function reload()
    {
        return [
            "action" => "reload"
        ];
    }

    /**
     * @param mixed $ids
     * @return array
     */
    protected function refresh($ids)
    {
        return [
            "action" => "refresh",
            "items" => (array)$ids
        ];
    }

    /**
     * @param string $bladeView
     * @param array $params
     * @return array
     */
    protected function view(string $bladeView, array $params = [])
    {
        return [
            "action" => "view",
            "html" => view($bladeView, $params)->render()
        ];
    }

    /**
     * Add a form field.
     *
     * @param SharpFormField $field
     * @return $this
     */
    protected function addField(SharpFormField $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Check if the current user is allowed to use this Command.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getGlobalAuthorization()
    {
        return $this->authorize();
    }

    /**
     * @return string|null
     */
    public function confirmationText()
    {
        return null;
    }

    /**
     * Build the optional Command form, calling ->addField()
     */
    public function buildForm()
    {
    }

    /**
     * Build the optional Command form layout.
     *
     * @param FormLayoutColumn $column
     */
    public function buildFormLayout(FormLayoutColumn &$column)
    {
    }

    /**
     * @return array
     */
    public function form()
    {
        $this->buildForm();

        return collect($this->fields)->map(function($field) {
            return $field->toArray();
        })->keyBy("key")->all();
    }

    /**
     * @return array|null
     */
    public function formLayout()
    {
        if(!$this->fields) {
            return null;
        }

        $column = new FormLayoutColumn(12);
        $this->buildFormLayout($column);

        if(empty($column->fieldsToArray()["fields"])) {
            foreach($this->fields as $field) {
                $column->withSingleField($field->key());
            }
        }

        return $column->fieldsToArray()["fields"];
    }

    /**
     * Validates the request in a form case.
     *
     * @param array $params
     * @param array $rules
     * @param array $messages
     * @throws ValidationException
     */
    public function validate(array $params, array $rules, array $messages = [])
    {
        $validator = app(Validator::class)->make($params, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException(
                $validator, new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }
    }
}