Shopping Cart для Yii2
=============
Компонент для реализации корзины моделей.

Корзина - объект контейнер, для хранения коллекции позиций, и методами для работы с этой коллекцией.

Состояние корзины между запросами хранится в сессии пользователя.

Установка и настройка
---------------------

###Установка:

php composer.phar require developeruz/shopping-cart-component "*"


### 1 вариант: Подключение через конфиг
Добавить:
```php
'components' => [
     'shoppingCart' =>
          [
              'class' => 'developeruz\shopping\EShoppingCart'
          ]
]
```
Использование в приложении:
```php
$cart = Yii::$app->shoppingCart;
```

### 2 вариант: Подключение по необходимости
```php
  $cart = Yii::createObject('developeruz\shopping\EShoppingCart');
  $cart->init();

  $book = Books::findOne(1);
  $cart->put($book);
```

Подготавливаем модель
---------------------
Модели, которым необходимо дать возможность добавления в корзину,
должны реализовать интерфейс `IECartPosition`:

```php
use developeruz\shopping\IECartPosition;

class Book extends CActiveRecord implements IECartPosition {
    ...
    public function getId()
    {
        return $this->id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getAviable()
    {
        return true;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getCostWithDiscount($quality)
    {
        if($quality > 10)
            return $quality * $this->getPrice() * 0.75;
        else return $quality * $this->getPrice();
    }
}
```

API
---

### EShoppingCart::put($position, $quantity)
Добавляет в корзину позицию товара в количестве $quantity.
Если позиция товара уже была в корзине, то данные модели обновляются, а количество увеличивается на $quantity

```php
$book = Books::findOne(1);
$cart->put($book); //в корзине 1 позиция с id=1 в количестве 1 единица.
$cart->put($book,2); //в корзине 1 позиция с id=1 в количестве 3 единицы.
$book2 = Books::findOne(2);
$cart->put($book2); //в корзине 2 позиции с id=1 и id=2
```

### EShoppingCart::update($position, $quantity)
Обновляет в корзине позицию товара.
Если позиция товара уже была в корзине, то данные модели обновляются, а количество установится в $quantity.
Если позиции не было в корзине, то она добавляется в ней.
Если установлено $quantity<1, то позиция удаляется из корзины

```php
$book = Books::findOne(1);
$cart->put($book); //в корзине 1 позиция с id=1 в количестве 1 единица.
$cart->update($book,2); //в корзине 1 позиция с id=1 в количестве 2 единицы.
```

### EShoppingCart::remove($key)
Удаляет позицию из корзины

```php
$book = Books::findOne(1);
$cart->put($book,2); //в корзине 1 позиция с id=1 в количестве 2 единицы.
$cart->remove($book); //в корзине нет позиций
```

### EShoppingCart::clear()
Очищает корзину

```php
$cart->clear();
```

### EShoppingCart::isEmpty()
Возвращает true, если корзина пустая.

```php
if($cart->isEmpty())
```

### EShoppingCart::getCount()
Возвращает количество позиций
```php
$cart->put($book,2);
$cart->put($book2,3);
$cart->getCount(); //2
```

### EShoppingCart::getItemsCount()
Возвращает количество товаров
```php
$cart->put($book,2);
$cart->put($book2,3);
$cart->getItemsCount(); //5
```
Может принимать в качестве не обязательного параметра позицию.
```
[php]
$cart->put($book,2);
$cart->put($book2,3);
$cart->getItemsCount($book); //2
```

### EShoppingCart::getCost()
Возвращает стоимость всей корзины
```php
$cart->put($book,2); //price=100
$cart->put($book2,1); //price=200
$cart->getCost(); //400
```
Может принимать в качестве не обязательного параметра позицию. И в этом случаи возвращает стоимость для данной позиции
```php
$cart->put($book,2); //price=100
$cart->put($book2,1); //price=200
$cart->getCost($book); //2*100 = 200
```

### EShoppingCart::getDiscountCost()
Возвращает стоимость с учетом скидки
```php
$cart->put($book,2); //price=100 скидка 25% при покупке 2 шт
$cart->put($book2,1); //price=200
$cart->getDiscountCost(); //350
```
Может принимать в качестве не обязательного параметра позицию. И в этом случаи возвращает стоимость для данной позиции
```php
$cart->put($book,2); //price=100
$cart->put($book2,1); //price=200
$cart->getDiscountCost($book); //2*100*0.75 = 150
```

### EShoppingCart::getPositions()
Возвращает массив позиций
```php
$positions = $cart->getPositions();
foreach($positions as $position) {
...
}
```
Каждая позиция содержит следующие данные:
item - текстовое обозначение товара, получаемое через $model->getTitle(),
quality - количество
price - цена за единицу товара ($model->getPrice())
cost - общая стоимость товара
cost_with_discount - общая стоимость с учетом скидок

Может принимать в качестве не обязательного параметра позицию.
```php
$positions = $cart->getPositions($book1);
```