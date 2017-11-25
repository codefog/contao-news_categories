# Template adjustments – News Categories Bundle

1. [Installation](installation.md)
2. [Configuration](configuration.md)
3. [Frontend modules](frontend-modules.md)
4. [**Template adjustments**](template-adjustments.md)
5. [News feeds](news-feeds.md)
6. [Multilingual features](multilingual-features.md)
7. [Insert tags](insert-tags.md)


## Display categories in news templates

You can display the categories in your custom `news_` templates. See the different possibilities below.

### Full category list

All the categories data is added to every news partial template which allows you to render any HTML markup 
of the categories you can imagine. The categories are available in the `$this->categories` variable.

**Pro tip:** access the news category model directly via `$category['model']`.

Example:

```php
<?php if ($this->categories): ?>
    <ul class="categories">
        <?php foreach ($this->categories as $category): ?>
            <li class="<?= $category['class'] ?>">
                <?php if ($category['image']): ?>
                    <figure class="image_container">
                        <?php $this->insert('picture_default', $category['image']->picture) ?>
                    </figure>
                <?php endif; ?>

                <?php if ($category['href']): ?>
                    <a href="<?= $category['href'] ?>" title="<?= $category['linkTitle'] ?>"><?= $category['name'] ?></a>
                <?php else: ?>
                    <span><?= $category['name'] ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
```

### Simple category list

To display a simple, plain-text category list you can use the array of `$this->categoriesList`: 

```php
<p class="categories">Categories: <?= implode(', ', $this->categoriesList) ?></p>
```

Example output markup:

```html
<p class="categories">Categories: Music, Sport</p>
```


## Categories list templates

The categories list module is similar to the navigation module. It also uses the two templates to render the markup:

1. `mod_newscategories` – the "wrapper" for the category navigation tree
2. `nav_newscategories` – the partial template used to generate the navigation items recursively

Both of the templates can be overwritten in the frontend module settings.
