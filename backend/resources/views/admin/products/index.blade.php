<h1>商品一覧</h1>

<table border="1">

<tr>
<th>ID</th>
<th>商品コード</th>
<th>商品名</th>
<th>SKU</th>
<th>価格</th>
</tr>

@foreach($products as $product)

<tr>
<td>{{ $product->id }}</td>
<td>{{ $product->code }}</td>
<td>{{ $product->name }}</td>
<td>{{ $product->sku }}</td>
<td>{{ $product->unit_price }}</td>
</tr>

@endforeach

</table>
