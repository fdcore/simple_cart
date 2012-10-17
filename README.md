Simple Cart
===========
Simple cart for ExpressionEngine used JSON Cookie for stored data.

# Insert Data

	{exp:simple_cart:insert name="test" price="500" entry_id="1"}

Params:
- name имя
- price цена
- entry_id номер записи
- qty количество
- id уникальный идентификатор

# Get Data
{exp:simple_cart:get}
    <h1>{name}: {entry_id}</h1>
    <p>цена {price} кол: {qty}</p>
    <hr>
{/exp:simple_cart:get}

# Delete all data
{exp:simple_cart:clean}

# Delete one item
{exp:simple_cart:delete id="sku_2324234"}
