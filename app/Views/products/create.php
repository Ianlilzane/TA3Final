<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h2>Add Product</h2>

<form action="/products/store" method="post">

    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Product Name</label>

        <input type="text"
               name="product_name"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>Price</label>

        <input type="number"
               name="price"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>Stock</label>

        <input type="number"
               name="stock"
               class="form-control">
    </div>

    <button class="btn btn-success">
        Save Product
    </button>

</form>

</body>
</html>