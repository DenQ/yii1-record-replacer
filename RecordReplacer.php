<?php
/**
 * Создает запись, если ее еще нет,
 * а если такая запись уже есть, то обновляет ее
 *
 * repo: https://github.com/DenQ/yii1-record-replacer
 * User: denq
 * Date: 31.10.15
 * Time: 20:43
 */

class RecordReplacer extends CComponent{

    public function init() {

    }

    /**
     * @var \yii\db\ActiveRecord null
     */
    public $model = null;
    public $params = [];
    public $primary = [];
    public $exclusion = [];

    /**
     * @var \yii\db\ActiveRecord null
     */
    public $resultModel = null;

    /**
     * Возвращает название модели
     * @param $model \yii\db\ActiveRecord
     * @return string
     */
    private function GetClassName() {
        $matches = explode('\\', $this->model->tableName());
        return $matches[count($matches)-1];
    }

    /**
     * @param $model \yii\db\ActiveRecord
     * @param $params mixed
     * @param $primary mixed
     * @return string
     */
    public function Run($model, $params, $primary = [], $exclusion = []) {
        $this->SetVariables($model, $params, $primary, $exclusion);
        if ( $this->Get() === null ) {
            $result = $this->Post();
        } else {
            $result = $this->Put();
        }
        $this->CleanVariables();
        return $result;
    }

    private function Post() {
        $params = $this->params;
        $this->model->setAttributes($params);
        if ($this->model->save()) {
            return $this->model;
        } return null;
    }

    private function Put() {
        $params = $this->DecorateParams($this->params);
        foreach($params as $key => $val) {
            if (!in_array($key, $this->exclusion)) {
                $params1[$key] = $val;
            }
        }
        $this->resultModel->setAttributes($params1);
        if ($this->resultModel->save()) {
            return $this->resultModel;
        } return null;
    }

    private function DecorateParams($params) {
        $params1 = [];
        foreach($params as $key => $val) {
            if (!array_key_exists($key, $this->exclusion)) {
                $params1[$key] = $val;
            }
        } return $params1;
    }

    private function Get() {
        $model = $this->model;
        $model = $model->find($this->GetCriteria());
        $this->resultModel = $model;
        return $model;
    }

    private function GetCriteria() {
        $criteria=new CDbCriteria;
        foreach($this->primary as $item) {
            if (array_key_exists($item, $this->params)) {
                if (is_numeric($this->params[$item]))
                    $criteria->addCondition( $item . ' = ' . $this->params[$item] );
                else
                    $criteria->addCondition( $item . " = '" . $this->params[$item] . "'");
            }
        } return $criteria;
    }

    private function SetVariables($model, $params, $primary = [], $exclusion = []) {
        $this->model = $model;
        $this->params = $params;
        $this->primary = $primary;
        $this->exclusion  = $exclusion ;
    }

    private function CleanVariables() {
        $this->model = null;
        $this->params = [];
        $this->primary = [];
        $this->exclusion = [];
        $this->resultModel = [];
    }

}