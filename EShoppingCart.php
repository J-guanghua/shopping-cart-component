<?php
namespace developeruz\shopping;

/**
 * IECartPosition
 * The idea is based on ShoppingCart for Yii1x (https://github.com/yiiext/shopping-cart-component)
 *
 * @author Elle <elleuz@gmail.com>
 * @version 0.1
 * @package ShoppingCart for Yii2
 *
 */
use Yii;
use yii\base\Component;

class EShoppingCart extends Component
{
    public $cartId = __CLASS__;

    private $basket = [];

    public function init()
    {
        parent::init();
        $this->basket = [];
        $this->restoreFromSession();
    }

    public function put($model, $quality=1)
    {
        $id = $model->getId();
        if (array_key_exists($id, $this->basket)) {
            $this->basket[$id]['quality'] += $quality;
        } else {
            $this->basket[$id]['quality'] = $quality;
            $this->basket[$id]['class'] = $model->className();
        }
        $this->saveInSession();
    }

    public function update($model, $quality)
    {
        if($quality<0)
            $this->remove($model);
        else {
            $id = $model->getId();
            if (array_key_exists($id, $this->basket)) {
                $this->basket[$id]['quality'] = $quality;
            }
            else {
                $this->put($model, $quality);
            }
        }
        $this->saveInSession();
    }

    public function remove($model)
    {
        $id = $model->getId();
        if (array_key_exists($id, $this->basket)) {
            unset($this->basket[$id]);
        }

        $this->saveInSession();
    }

    public function clear()
    {
        $this->basket = [];
        Yii::$app->session->remove($this->cartId);
    }

    public function isEmpty()
    {
        return empty($this->basket);
    }

    public function getCount()
    {
        return count($this->basket);
    }

    public function getItemsCount($model = '')
    {
        $result = 0;
        if (empty($model)) {
            foreach ($this->basket as $items) {
                $result += $items['quality'];
            }
        } else {
            $id = $model->getId();
            $result = $this->basket[$id]['quality'];
        }
        return $result;
    }

    public function getCost($model = '')
    {
        $result = 0;
        if (empty($model)) {
            foreach ($this->basket as $id => $item) {
                $itemModel = $this->getModel($item['class'], $id);
                $result += ($item['quality'] * $itemModel->getPrice());
            }
        } else {
            $id = $model->getId();
            $result = $this->basket[$id]['quality'] * $model->getPrice();
        }
        return $result;
    }

    public function getDiscountCost($model = '')
    {
        $result = 0;
        if (empty($model)) {
            foreach ($this->basket as $id => $item) {
                $itemModel = $this->getModel($item['class'], $id);
                $result += $itemModel->getCostWithDiscount($item['quality']);
            }
        } else {
            $id = $model->getId();
            $result = $model->getCostWithDiscount($this->basket[$id]['quality']);
        }
        return $result;
    }

    public function getPositions($model = '')
    {
        $result = [];
        if (empty($model)) {
            foreach ($this->basket as $id => $item) {
                $itemModel = $this->getModel($item['class'], $id);
                $result[] = [
                    'item' => $itemModel->getTitle(),
                    'quality' => $item['quality'],
                    'price' => $itemModel->getPrice(),
                    'cost' => ($item['quality'] * $itemModel->getPrice()),
                    'cost_with_discount' => $itemModel->getCostWithDiscount($item['quality'])
                ];
            }
        } else {
            $id = $model->getId();
            $result = [
                'item' => $model->getTitle(),
                'quality' => $this->basket[$id]['quality'],
                'price' => $model->getPrice(),
                'cost' => ($this->basket[$id]['quality'] * $model->getPrice()),
                'cost_with_discount' => $model->getCostWithDiscount($this->basket[$id]['quality'])
            ];
        }
        return $result;
    }

    private function restoreFromSession()
    {
        if (Yii::$app->session->has($this->cartId)) {
            $productList = unserialize(Yii::$app->session[$this->cartId]);
            foreach ($productList as $id => $p) {
                if (isset($p['class'])) {
                    $this->add($p['class'], $id, (!empty($p['quality']) ? $p['quality'] : 1));
                }
            }
        }
    }

    private function add($class, $id, $quality)
    {
        $model = $this->getModel($class, $id);
        if (!empty($model) && $model->getAviable()) {
            $this->put($model, $quality);
        }
    }

    private function saveInSession()
    {
        Yii::$app->session[$this->cartId] = serialize($this->basket);
    }

    private function getModel($class, $id)
    {
        return $class::findOne($id);
    }

} 