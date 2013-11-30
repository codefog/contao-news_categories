news_categories Contao Extension
================================

Extend the Contao news module with categories. This extension adds a new header button in the news module. You can create categories there and then assign each news item to any of the categories, multiple selections are possible. In the front end make sure that your news list module is filterable by categories!

The extension is sponsored by Martin Schaffner from [Webcontext](http://www.webcontext.com).
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
	<li class="category_<?php echo $category['id']; ?>"><?php echo $category['frontendTitle'] ? $category['frontendTitle'] : $category['title']; ?></li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
```

### Contao compatibility
- Contao 3.0
- Contao 3.1
- Contao 3.2

### Available languages
- English
- French
- German
- Italian
- Polish

### Support us
We put a lot of effort to make our extensions useful and reliable. If you like our work, please support us by liking our [Facebook profile](http://facebook.com/Codefog), following us on [Twitter](https://twitter.com/codefog) and watching our [Github activities](http://github.com/codefog). Thank you!

### Copyright
The extension was developed by [Codefog](http://codefog.pl) and is distributed under the Lesser General Public License (LGPL). Feel free to contact us using the [website](http://codefog.pl) or directly at info@codefog.pl.