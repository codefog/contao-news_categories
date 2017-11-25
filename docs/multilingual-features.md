# Multilingual features – News Categories Bundle

1. [Installation](installation.md)
2. [Configuration](configuration.md)
3. [Frontend modules](frontend-modules.md)
4. [Template adjustments](template-adjustments.md)
5. [News feeds](news-feeds.md)
6. [**Multilingual features**](multilingual-features.md)
7. [Insert tags](insert-tags.md)


## Requirements

To enable the multilingual features make sure you have installed the necessary packages listed in the
[Installation](installation.md) chapter.


## Category language management

You can manage the multilingual records of categories inside the category edit form. The language switch bar is located
at the top of the form:

![](images/category-language.png)


## Translate category URL parameter

If you have the website in other language than English or have multiple languages on your installation, you can set 
the custom category URL parameter per each website root:

![](images/category-page-settings.png)

Take a look at the example table below:

Website root | Field value | Result URL 
--- | --- | ---
English | empty (default to "category") | /en/news/category/music.html
German | kategorie | /de/news/kategorie/music.html
Polish | kategoria | /pl/news/kategoria/music.html
