<?php

namespace backend\models\search;

use common\models\Families;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class FamilySearch extends Families
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['name', 'address'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $limit = '')
    {
        if ($limit != '') {
            $query = Families::find()->limit($limit);
        } else {
            $query = Families::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        //echo var_dump($this->zip_code);
        $query->andFilterWhere([
            'id'     => $this->id,
            'status' => $this->status,
            /*'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'logged_at' => $this->logged_at*/
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'address', $this->address]);
        /*->andFilterWhere(['in', 'zip_code', $this->zip_code])
        /*->andFilterWhere(['like', 'email', $this->email])
        ->andFilterWhere(['like', 'position_for', $this->position_for]);
->andFilterWhere(['like', 'username', $this->username])
        ->andFilterWhere(['like', 'auth_key', $this->auth_key])
        ->andFilterWhere(['like', 'password_hash', $this->password_hash])*/
        return $dataProvider;
    }
}
