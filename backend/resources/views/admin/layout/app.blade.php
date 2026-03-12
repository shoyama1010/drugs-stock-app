<!DOCTYPE html>
<html>
<head>

<title>DrugStore Stock</title>

<style>

body{
margin:0;
font-family:sans-serif;
display:flex;
}

.sidebar{
width:220px;
background:#1f2937;
color:white;
height:100vh;
}

.sidebar h2{
padding:20px;
}

.sidebar a{
display:block;
padding:15px;
color:white;
text-decoration:none;
}

.sidebar a:hover{
background:#374151;
}

.main{
flex:1;
padding:30px;
background:#f3f4f6;
}

table{
width:100%;
background:white;
border-collapse:collapse;
}

th,td{
padding:10px;
border-bottom:1px solid #ddd;
}

</style>

</head>

<body>

<div class="sidebar">

<h2>DrugStore</h2>

<a href="/admin/dashboard">ダッシュボード</a>
<a href="/admin/products">商品管理</a>
<a href="/admin/stocks">在庫管理</a>
<a href="/admin/stocks/in">入庫処理</a>
<a href="/admin/stocks/out">出庫処理</a>
<a href="/admin/transactions">履歴</a>

</div>

<div class="main">

@yield('content')

</div>

</body>
</html>
