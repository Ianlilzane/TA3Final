<h1>Products Page</h1>

<?php if(isset($products)): foreach($products as $product): ?>

<p>
    <?= $product['product_name']; ?>
</p>

<?php endforeach; ?>
<?php endif; ?>