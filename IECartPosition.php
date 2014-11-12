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
interface IECartPosition
{
    /**
     * @return mixed id
     */
    public function getId();

    /**
     * @return float price
     */
    public function getPrice();

    /**
     * @return boolean available
     */
    public function getAvailable();

    /**
     * @return string title
     */
    public function getTitle();

    /**
     * @return float price with discount
     */
    public function getCostWithDiscount($quality);
}
