news_categories Contao Extension
================================

Extend the Contao news module with categories. This extension adds a new header button in the news module. You can create categories there and then assign each news item to any of the categories, multiple selections are possible. In the front end make sure that your news list module is filterable by categories!

The extension is sponsored by Martin Schaffner from Webcontext.
Demo page http://www.webcontext.info/de/news-kategorien.html

You can display the categories in your *news_* template:

```php
// Short hand version
// Produces "Categories: Foo, Bar, Foobar"
<p class="categories">Categories: <?php echo implode(', ', $this->categoriesList); ?></p>

// Full version
// Produces a list of categories
<?php if ($this->categories): ?>
<ul class="categories">
	<?php foreach ($this->categories as $category): ?>
	<li class="category_<?php echo $category['id']; ?>"><?php echo $category['title']; ?></li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
```

### Contao compatibility
- Contao 3.0
- Contao 3.1

### Available languages
- English
- French
- German
- Italian
- Polish