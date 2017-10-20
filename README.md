# Contao news categories

This fork is based on [codefog/contao-news_categories](https://github.com/codefog/contao-news_categories) version `2.8.6` and provides several new functionalities.

## New features 

- primary category support (primary category can be set on news, otherwise first category in list is primary category)
- category jumpTo based on news_archive (can be set on each news category for each news archive)
- news url jumpTo based on news_archive and primary category (can be set on each news category for each news archive) -> news url will be linked to the primary category jumpTo page instead of archive jumpTo page
- news categories can now have a teaser (also based on news_archive, can be set on each news category for each news archive)

### Inserttags

Tag | Arguments | Description
------ | ---- | ------- 
`{{news_archive_category_page::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with the ID of the linked category page based on the given news archive id.
`{{news_archive_category_page_url::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with the URL of the linked category page based on the given news archive id. `<a href="{{news_archive_category_page_url::12::1}}">Click here</a>`
`{{news_archive_category_page_title::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with the title of the linked category page based on the given news archive id: `<a title="{{news_archive_category_page_title::12::1}}">Click here</a>.`
`{{news_archive_category_page_link::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with a link of the linked category page based on the given news archive id.  
`{{news_archive_category_page_name::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with the name of the linked category page based on the given news archive id.
`{{news_archive_category_page_link_open::*::*}}` | 1. category ID or alias, 2. news archive id | Will be replaced with the opening tag of a link of the linked category page based on the given news archive id: {{news_archive_category_page_link_open::12::1}}Click here{{link_close}}
`{{news_archive_category_teaser::*::*}}` | 1. category ID or alias, 2. news archive id | This tag will be replaced with the teaser text of the linked category page based on the given news archive id.
`{{news_category_page::*::*}}` | 1. category ID or alias | This tag will be replaced with the ID of the linked category.
`{{news_category_alias::*::*}}` | 1. category ID or alias | This tag will be replaced with the alias of the linked category.
`{{news_category_teaser::*::*}}` | 1. category ID or alias | This tag will be replaced with the teaser text of the linked category.
`{{news_category_link::*}}` | 1. news ID or alias | This tag will be replace with a news link for the given news, based on the primary category jumpTo (if set) for the news parent id (archive).
`{{news_category_link_open::*}}` | 1. news ID or alias | Will be replaced with the opening tag of a news link for the given news, based on the primary category jumpTo (if set) for the news parent id (archive).
`{{news_category_url::*}}` | 1. news ID or alias | This tag will be replace with a news url for the given news, based on the primary category jumpTo (if set) for the news parent id (archive).