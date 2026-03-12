@extends('admin.layout.app')

@section('content')

<h1>在庫一覧</h1>
<table>
<tr>
<th>商品コード</th>
<th>商品名</th>
<th>ロケーション</th>
<th>在庫</th>
</tr>

@foreach($stocks as $stock)
<tr>
<td>{{ $stock->stockLot->product->code }}</td>

<td>{{ $stock->stockLot->product->name }}</td>
<td>

{{ $stock->location->aisle }}
-
{{ $stock->location->shelf }}
-
{{ $stock->location->position }}
</td>

<td>{{ $stock->quantity_remaining }}</td>
</tr>
@endforeach

</table>
@endsection
