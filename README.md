# Introduction

**Laravel Filters** is a **modular**, **scalable** filtering engine designed to keep your codebase clean, maintainable,
and easy to extend.
It eliminates **bloated controllers** and **scattered if statements** with a structured, composable approach to dynamic,
request-driven query filtering.
By isolating filter logic into well-defined, **single-responsibility** classes, it enforces true **separation of
concerns** while staying fully aligned with **SOLID** and **DRY** principles.

Whether you're building RESTful APIs, admin panels, or data-intensive applications, **Laravel Filters** fits right in —
offering the flexibility and structure needed for modern Laravel projects:

🧱 Decouple business logic with focused, single-responsibility filter classes.

🔄 Easily extend and customize filters to meet your unique requirements.

🧠 Compose complex query conditions effortlessly — even across nested relationships

↕️ Apply multiple sorts dynamically and cleanly, directly from request input

⚙️ Seamlessly integrate into your existing Laravel codebase with minimal setup

Designed for scalability, and developer satisfaction.

# Requirements

- PHP >= 8.1
- Laravel >= 10.0

# Installation

1. Install the package via Composer:

```bash
composer require redalabs/laravel-filters
```

# Table of Contents

* [Getting Started](#getting-started)
    * [Creating a Custom Utilities Service](#create-a-custom-utilities-service)
    * [Defining Filters](#defining-filters)
    * [Reusable Filter Classes](#reusable-filter-classes)
    * [Defining Sorts](#defining-sorts)
* [Using the Utilities Service](#using-the-utilities-service)
* [Building the Query](#building-the-query)
* [Components](#components)
    * [Utilities Service](#utilities-service)
    * [Criteria](#criteria)
    * [Conditions](#condition)
    * [Joins](#joins)
    * [Sorts](#sorts)

---

# Getting Started

## Create a Custom Utilities Service

To begin, define your own service class that extends the base `UtilitiesService` class:

```php
use RedaLabs\LaravelFilters\UtilitiesService;

class MyUtilitiesService extends UtilitiesService
{
}
`````

---

## Defining Filters

Define filters inside the `filters()` method of your service class. Each filter is a `key-value` pair where:

* The **key** represents the query parameter.
* The **value** is either the method name to handle the filter or a `Filter` instance.

```php
class MyUtilitiesService extends UtilitiesService
{
    public function filters(): array
    {
        return [
            'product_name' => 'productNameFilter',
        ];
    }

    public function productNameFilter(Criteria $criteria, mixed $value)
    {
        $criteria->appendCondition(new Condition('products.name', 'like', "%$value%"));
    }
}
```

Each filter method receives two arguments:

`Criteria $criteria`: The criteria instance for appending conditions & joins.

`mixed $value`: The filter value from the request.

🔎 For more on how Criteria works, [see the Criteria section](#criteria)

🔒 **Tip:**
Always validate filter values using Form Requests, or another approach you prefer.

📘 [Explore Available Conditions →](#Conditions) // todo check if could be removed to end of section.

### Applying Joins in Filters

Need to use Join's in filters? No problem — just append them inside your filter method:

```php
$criteria->appendJoin(
    new Join('product_categories', 'categories.id = products.category_id')
);

$criteria->appendCondition(
    new Condition('product_categories.name', 'like', "%$value%")
);
```

You can even attach conditions directly to the join:

```php
$criteria->appendJoin(
    (new Join('product_categories', 'categories.id = products.category_id'))
        ->appendCondition(new Condition('product_categories.is_active', '=', 1))
        ->appendCondition(...)
);
```

✅ **Best Practice**: To avoid overwriting joins when reusing the same one in multiple filters, always check if it
already exists:

```php
if (!$criteria->isJoinExists('product_categories')) {
    $criteria->appendJoin(new Join('product_categories', 'categories.id = products.category_id'));
}
```

🔒 **Important:**
Appending a join with an existing name will overwrite the previous one.

#### Example with Multiple Filters Using Joins

```php
class MyUtilitiesService extends UtilitiesService
{
    public function filters(): array
    {
        return [
            'category_name' => 'categoryNameFilter',
            'has_multiple_categories' => 'multipleCategoriesFilter',
        ];
    }

    public function categoryNameFilter(Criteria $criteria, mixed $value)
    {
        if (!$criteria->isJoinExists('product_categories')) {
            $criteria->appendJoin(new Join('product_categories', 'categories.id = products.category_id'));
        }

        $criteria->appendCondition(new Condition('product_categories.name', 'like', "%$value%"));
    }

    public function multipleCategoriesFilter(Criteria $criteria, mixed $value)
    {
        if (!$value) return;

        if (!$criteria->isJoinExists('product_categories')) {
            $criteria->appendJoin(new Join('product_categories', 'categories.id = products.category_id'));
        }

        $criteria->appendCondition(new AggregationCondition('COUNT(product_categories.id)', '>', 1));
    }
}
```

📘 [Explore Available Joins →](#joins)

---

## Reusable Filter Classes

You can reuse common filters across services by implementing the `Filter` interface:

````php
class ProductCategoryNameFilter implements Filter
{
    public function apply(Criteria $criteria, mixed $value)
    {
        if (!$criteria->isJoinExists('product_categories')) {
            $criteria->appendJoin(new Join('product_categories', 'categories.id = products.category_id'));
        }
        $criteria->appendCondition(new Condition('product_categories.name', 'like', "%$value%"));
    }
````

Use the filter class like this:

```php
class MyUtilitiesService extends UtilitiesService
{
    public function filters(): array
    {
        return [
            'category_name' => new ProductCategoryNameFilter(),
        ];
    }
}
```

You can reuse it in other services too:

````php
class DashboardUtilitiesService extends UtilitiesService
{
    public function filters(): array
    {
        return [
            'category' => new ProductCategoryNameFilter(),
        ];
    }
}
````

🔒 **Important:**
📌 Make sure your filter class implements the `Filter` interface and its `apply()` method.
---

## Defining Sorts

Define available sorts inside the `sorts()` method of your service class. Each sort is a `key-value` pair where:

* The **key** represents the sort name.
* The **value** is either the **method name** to return the sort, or the value itself will be used as the **column name
  **.

```php
class MyUtilitiesService extends UtilitiesService
{
    public function sorts(): array
    {
        return [
            'name' => 'customNameSort',
            'created_at' => 'created_at',
        ];
    }

    public function customNameSort(string $direction)
    {
        return new Sort('products.name', $direction);
    }
}
```

🔒 **Note:**
Sort parameters are expected in the following format:

```
/products?sorts[0][field]=name&&sorts[1][field]=created_at&sorts[1][direction]=desc
```

* The `direction` key is optional (asc is default).
* The sorts key can be customized by overriding the `$sortsKey` property:

```php
class MyUtilitiesService extends UtilitiesService
{
    protected string $sortsKey = 'your_custom_sort_key';
    ...
}
```

📌 If a method is not found for a sort key, the key itself will be used as the column name.

📘 [Explore Available Sorts →](#sorts)

---
# Using the Utilities Service

Inject and use it in your controller:

```php
class ProductController extends Controller
{
    public function index(Request $request, ProductUtilitiesService $utilitiesService)
    {
        $criteria = $utilitiesService
            ->applyFilters()
            ->applySorts()
            ->getCriteria();

        // Pass criteria to repository or query builder
    }
}
```

---

# Building the Query

Use the `Criteria` object to apply on Builder

```php
class ProductRepository
{
    public function getProducts(Criteria $criteria)
    {
        $query = Product::query();
        $criteria->applyOnBuilder($query);

        return $query->get();
    }
}
```

> ✅ You can use `applyOnBuilder()` anywhere you're building your query—not limited to repositories.

# Components

## Utilities Service

The `UtilitiesService` class is an abstract base class that provides a structured way to handle filtering and sorting in
your Laravel applications. It acts as a bridge between HTTP requests and the Criteria class, making it easy to implement
filtering and sorting functionality in your services.

### Purpose

The `UtilitiesService` class serves as a foundation for building filterable and sortable services. It allows you to:

- Automatically process filter and sort parameters from HTTP requests.
- Define available filters and sorts for your service.
- Apply filters and sorts to your queries in a consistent way.

### Available Methods

#### `getCriteria(): Criteria`

Returns the current Criteria instance.

- Returns: The current Criteria instance

#### `fresh(): self`

Creates a new Criteria instance and resets the service.

- Returns: The current service instance

#### `applyFilters(): UtilitiesService`

Applies all valid filters from the request to the Criteria instance.

- Returns: The current service instance

#### `applySorts(): self`

Applies all valid sorts from the request to the Criteria instance.

- Returns: The current service instance

### Protected Methods to Override

#### `filters(): array`

Define the available filters for your service.

- Returns: An array of filter keys and their corresponding filter classes or method names

```php
protected function filters(): array
{
    return [
        'status' => StatusFilter::class,
        'date_range' => 'applyDateRangeFilter',
        'search' => SearchFilter::class
    ];
}
```

#### `sorts(): array`

Define the available sorts for your service.

- Returns: An array of sort keys and their corresponding field names or method names

```php
protected function sorts(): array
{
    return [
        'created_at' => 'created_at',
        'name' => 'users.name',
        'custom_sort' => 'applyCustomSort'
    ];
}
```

### Usage Examples

#### Basic Implementation

```php
use RedaLabs\LaravelFilters\UtilitiesService;
use RedaLabs\LaravelFilters\Criteria;
use Illuminate\Http\Request;

class UserService extends UtilitiesService
{
    protected string $defaultSortDirection = 'desc';

    public function __construct(Criteria $criteria, Request $request)
    {
        parent::__construct($criteria, $request);
    }

    protected function filters(): array
    {
        return [
            'status' => StatusFilter::class,
            'role' => RoleFilter::class,
            'search' => 'applySearchFilter'
        ];
    }

    protected function sorts(): array
    {
        return [
            'name' => 'users.name',
            'email' => 'users.email',
            'created_at' => 'users.created_at'
        ];
    }

    protected function applySearchFilter(Criteria $criteria, string $value): void
    {
        $criteria->appendCondition(new Condition('name', 'like', "%{$value}%"));
    }
}
```

#### Complex Implementation

```php
use RedaLabs\LaravelFilters\UtilitiesService;
use RedaLabs\LaravelFilters\Criteria;
use Illuminate\Http\Request;

class PostService extends UtilitiesService
{
    protected string $defaultSortDirection = 'desc';
    protected string $sortsKey = 'order_by'; // Customize the sort parameter key

    public function __construct(Criteria $criteria, Request $request)
    {
        parent::__construct($criteria, $request);
    }

    protected function filters(): array
    {
        return [
            'status' => StatusFilter::class,
            'category' => CategoryFilter::class,
            'date_range' => 'applyDateRangeFilter',
            'author' => 'applyAuthorFilter',
            'tags' => TagsFilter::class
        ];
    }

    protected function sorts(): array
    {
        return [
            'created_at' => 'posts.created_at',
            'title' => 'posts.title',
            'views' => 'applyViewsSort',
            'popularity' => 'applyPopularitySort'
        ];
    }

    protected function applyDateRangeFilter(Criteria $criteria, array $value): void
    {
        $criteria->appendCondition(new BetweenCondition(
            'created_at',
            [$value['start'], $value['end']]
        ));
    }

    protected function applyAuthorFilter(Criteria $criteria, int $authorId): void
    {
        $criteria->appendJoin(new Join('users', 'id', '=', 'posts.user_id', 'inner'));
        $criteria->appendCondition(new Condition('user_id', '=', $authorId));
    }

    protected function applyViewsSort(string $direction): BaseSort
    {
        return new RawSort('views_count + likes_count', [], $direction);
    }

    protected function applyPopularitySort(string $direction): BaseSort
    {
        return new RawSort('(views_count * 0.7) + (likes_count * 0.3)', [], $direction);
    }
}
```

### Best Practices

- Create dedicated filter classes for complex or frequently reused filters.
- Always validate filter and sort parameters to ensure data integrity.
- Be mindful of security risks when handling user-provided input.
- Keep filtering and sorting logic clean, focused, and limited to a single responsibility.

## Criteria

The `Criteria` class is the main orchestrator of the Laravel Filters package. It manages and applies joins, conditions,
and sorts to your query builder in a structured and organized way.

### Purpose

The Criteria class serves as a container and manager for all query modifications. It allows you to:

- Build complex queries by combining multiple conditions, joins, and sorts
- Maintain a clean and organized query building process
- Apply all modifications to your query builder in the correct order
- Manage the relationships between different query components

### Available Methods

#### `appendJoin(BaseJoin $join, int $sort = 100): self`

Adds a join to the criteria with an optional sort order.

- `$join`: The join instance to add
- `$sort`: The order in which the join should be applied (lower numbers are applied first). Defaults to 100

#### `appendSort(BaseSort $sort): self`

Adds a sort to the criteria.

- `$sort`: The sort instance to add

#### `removeJoinIfExists(string $joinName): self`

Removes a join from the criteria if it exists.

- `$joinName`: The name of the join to remove

#### `isJoinExists(string $joinName): bool`

Checks if a join exists in the criteria.

- `$joinName`: The name of the join to check
- Returns: `true` if the join exists, `false` otherwise

#### `appendCondition(BaseCondition $condition): self`

Adds a condition to the criteria.

- `$condition`: The condition instance to add

#### `applyOnBuilder(Builder $builder): Builder`

Applies all joins, conditions, and sorts to the query builder in the correct order.

- `$builder`: The query builder instance to modify
- Returns: The modified query builder

### Example

```php
use RedaLabs\LaravelFilters\Criteria;
use RedaLabs\LaravelFilters\Joins\Concretes\Join;
use RedaLabs\LaravelFilters\Conditions\Concretes\{
    Condition,
    BetweenCondition,
    InCondition,
    GroupConditions
};
use RedaLabs\LaravelFilters\Sorts\Concretes\Sort;

$criteria = new Criteria();

// Add multiple joins with different priorities
$criteria->appendJoin(new Join('users', 'id', '=', 'posts.user_id', 'inner'), 100);
$criteria->appendJoin(new Join('categories', 'id', '=', 'posts.category_id', 'left'), 200);

// Add complex conditions
$criteria->appendCondition(new Condition('status', '=', 'active'));
$criteria->appendCondition(new BetweenCondition('created_at', ['2023-01-01', '2023-12-31']));
$criteria->appendCondition(new InCondition('category_id', [1, 2, 3]));

// Add grouped conditions
$groupConditions = new GroupConditions([
    new Condition('views', '>', 1000),
    new Condition('likes', '>', 100, 'or')
]);
$criteria->appendCondition($groupConditions);

// Add multiple sorts
$criteria->appendSort(new Sort('created_at', 'desc'));
$criteria->appendSort(new Sort('views', 'desc'));

// Apply to a query builder
$query = DB::table('posts');
$query = $criteria->applyOnBuilder($query);

// The resulting query will be equivalent to:
// SELECT * FROM posts
// INNER JOIN users ON users.id = posts.user_id
// LEFT JOIN categories ON categories.id = posts.category_id
// WHERE status = 'active'
//   AND created_at BETWEEN '2023-01-01' AND '2023-12-31'
//   AND category_id IN (1, 2, 3)
//   AND (views > 1000 OR likes > 100)
// ORDER BY created_at DESC, views DESC
```

## Conditions

- [Basic Conditions](#basic-conditions)
- [JSON Conditions](#json-conditions)
- [Aggregation Conditions](#aggregation-conditions)
- [Group Conditions](#group-conditions)
- [Special Conditions](#special-conditions)

## Basic Conditions

### Condition

**Purpose**: The fundamental building block for creating WHERE clauses in your queries. It handles basic comparison
operations between a field and a value.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE)
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;

// Simple equality check
$condition = new Condition('name', '=', 'John');

// Greater than comparison
$condition = new Condition('age', '>', 18);

// LIKE query
$condition = new Condition('email', 'like', '%@gmail.com');

// You can optionally set the boolean operator to either 'and' or 'or'; it defaults to 'and'.
$condition = new Condition('status', '=', 'active', 'or');

```

### ColumnCondition

**Purpose**: Used when you need to compare two columns in the same table or across joined tables.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE)
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\ColumnCondition;

// Compare two columns
$condition = new ColumnCondition('products.price', '>', 'categories.min_price');

// You can optionally set the boolean operator to either 'and' or 'or'; it defaults to 'and'.
$condition = new ColumnCondition('products.stock', '<', 'suppliers.min_stock', 'or');
```

## JSON Conditions

### JsonContainCondition

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.
- `bool $not`: Indicates whether to negate the condition (true for NOT logic). Defaults to false

**Purpose**: Checks if a JSON array contains a specific value. Useful for querying JSON columns that store arrays.

#### Parameters:

- `$field`: The database column name to apply the condition on
- `$value`: The value to compare against
- `$boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to 'and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonContainCondition;

// Check if tags array contains 'php'
$condition = new JsonContainCondition('tags', 'php');

// Check if preferences->languages contains 'en'
$condition = new JsonContainCondition('preferences->languages', 'en');
```

### JsonContainsKeyCondition

**Purpose**: Verifies if a JSON object contains a specific key. Useful for checking the existence of properties in JSON
data.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.
- `bool $not`: Indicates whether to negate the condition (true for NOT logic). Defaults to false

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonContainsKeyCondition;

// Check if metadata has 'last_login' key
$condition = new JsonContainsKeyCondition('metadata', 'last_login');

// Check nested key
$condition = new JsonContainsKeyCondition('preferences->notifications', 'email');
```

### JsonLengthCondition

**Purpose**: Compares the length of a JSON array. Useful for filtering based on array size.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE)
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonLengthCondition;

// Check if tags array has more than 2 items
$condition = new JsonLengthCondition('tags', '>', 2);

// Check if preferences->languages has exactly 3 items
$condition = new JsonLengthCondition('preferences->languages', '=', 3);
```

### JsonOverlapCondition

**Purpose**: Checks if two JSON arrays have any elements in common. Useful for finding records with matching array
elements.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.
- `bool $not`: Indicates whether to negate the condition (true for NOT logic). Defaults to false

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\JsonOverlapCondition;

// Check if tags overlap with ['php', 'laravel']
$condition = new JsonOverlapCondition('tags', ['php', 'laravel']);

// Check if skills overlap with required skills
$condition = new JsonOverlapCondition('skills', ['php', 'mysql', 'redis']);
```

## Aggregation Conditions

### AggregationCondition

**Purpose**: Applies conditions on aggregated values (COUNT, SUM, AVG, etc.). Essential for filtering based on grouped
data.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE)
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\AggregationCondition;

// Filter products with more than 5 orders
$condition = new AggregationCondition('COUNT(id)', '>', 5);

// Filter categories with total sales over 1000
$condition = new AggregationCondition('SUM(price)', '>', 1000);

// Filter products with average rating above 4.5
$condition = new AggregationCondition('AVG(rating)', '>', 4.5);
```

## Group Conditions

### GroupConditions

**Purpose**: Groups multiple conditions together with a logical operator (AND/OR). Enables complex query composition.

#### Parameters:

- `BaseCondition[] $conditions`: An array of condition instances to be grouped together.
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\GroupConditions;

// Basic group with AND
$group = new GroupConditions([
    new Condition('status', '=', 'active'),
    new Condition('age', '>', 18)
], 'and');

// Nested groups with mixed operators
$group = new GroupConditions([
    new GroupConditions([
        new Condition('status', '=', 'active'),
        new Condition('age', '>', 18)
    ], 'or'),
    new Condition('is_verified', '=', true)
], 'and');
```

> **Note**
>
> The `GroupConditions` class does **not** support mixing different condition types. Specifically, you **cannot combine
** `AggregationCondition` instances with standard (non-aggregation) `Condition` instances in the same group.
>
> If mixed condition types are provided, a `MixedGroupConditionException` will be thrown to enforce consistency and
> prevent ambiguous behavior.

## Special Conditions

### BetweenCondition

**Purpose**: Creates a BETWEEN clause for range queries. Useful for filtering values within a specific range.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `array $values`: An array containing exactly two elements, representing the lower and upper bounds.
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\BetweenCondition;

// Filter products with price between 10 and 100
$condition = new BetweenCondition('price', [10,100]);

// Filter users with age between 18 and 65
$condition = new BetweenCondition('age', [18, 65]);
```

> **Note**
>
> The `BetweenCondition` requires the value to be an **array containing exactly two elements** — representing the lower
> and upper bounds of the range for a `WHERE BETWEEN` comparison.
>
> If the provided array does not contain exactly two elements, an `InvalidArgumentException` will be thrown to ensure
> proper condition formatting.

### DateCondition

**Purpose**: Specialized condition for date comparisons. Handles date formatting and comparison.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE)
- `mixed $value`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\DateCondition;

// Filter records created after specific date
$condition = new DateCondition('created_at', '>', '2024-01-01');

// Filter records created on specific date
$condition = new DateCondition('created_at', '=', '2024-01-01');
```

### InCondition

**Purpose**: Creates an IN clause for checking if a value exists in a set of values.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `array $values`: The value to compare against
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.
- `bool $not`: Indicates whether to negate the condition (true for NOT logic). Defaults to false

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\InCondition;

// Filter users with specific statuses
$condition = new InCondition('status', ['active', 'pending']);

// Filter products in specific categories
$condition = new InCondition('category_id', [1, 2, 3]);
```

### NullCondition

**Purpose**: Checks if a field is NULL or NOT NULL. Useful for filtering records based on the presence/absence of data.

#### Parameters:

- `string $field`: The database column name to apply the condition on
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions
- `bool $not`: Indicates whether to negate the condition (true for NOT logic). Defaults to false

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\NullCondition;

// Filter records where deleted_at is NULL
$condition = new NullCondition('deleted_at');

// Filter records where deleted_at is NOT NULL
$condition = new NullCondition('deleted_at', 'and', true);
```

### RawCondition

**Purpose**: Allows direct SQL conditions when complex queries are needed. Use with caution and proper parameter
binding.

#### Parameters:

- `string $expression`: The raw SQL expression to be used as a condition.
- `array $bindings`: The values to bind to the placeholders within the raw SQL expression. Defaults to an empty array
- `string $boolean`: The logical operator ('and' or 'or') to use when combining with other conditions. Defaults to '
  and'.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\RawCondition;

// Complex condition with parameters
$condition = new RawCondition('price > ? AND status = ?', [100, 'active']);

// Date comparison with functions
$condition = new RawCondition('DATE(created_at) = ?', ['2024-01-01']);
```

🔒 **Important:**
Use parameter binding (?) with RawCondition to prevent SQL injection.

### WhenCondition

**Purpose**: Conditionally applies another condition based on a boolean verification. Useful for dynamic query building.

#### Parameters:

- `bool $verification`: A boolean flag that determines whether the condition should be applied.
- `BaseCondition $condition`: The condition to apply if the verification passes.

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\WhenCondition;

// Apply condition only for admin users
$condition = new WhenCondition($isAdmin, new Condition('role', '=', 'admin'));

// Apply condition based on feature flag
$condition = new WhenCondition($featureEnabled, new Condition('feature_id', '=', $featureId));
```

## Best Practices

1. **Type Safety**: Always use proper type hints and validate input values before creating conditions.
2. **JSON Operations**: Ensure your database supports JSON operations before using JSON-related conditions.
3. **Performance**: Consider the impact of complex conditions and nested groups on query performance.
4. **Condition Organization**:

- Group related conditions together
- Use `GroupConditions` for complex logical combinations
- Consider the order of conditions for optimal query performance

---

## Joins

The Laravel Filters package provides two types of joins to help you build complex queries with proper table
relationships.

### Join

**Purpose**: Creates a standard SQL JOIN clause with conditions. This is the most commonly used join type, allowing you
to specify the join conditions and add additional conditions to the join clause.

#### Parameters:

- `string $table`: The table to join with.
- `array $first`: The first column in the join condition.
- `string $operator`: The comparison operator (=,!=,<>,>,<,>=,<=,LIKE,NOT LIKE).
- `string $second`: The second column in the join condition.
- `string $type`: The type of join to perform (inner, left, right). Defaults to 'inner'.
- `string|null $name`: Optional name for the join. If not provided, the table name is used.

#### Available Methods

`appendCondition(BaseCondition $condition): self`

- Adds a condition to the join clause
- Returns the join instance for method chaining

   ```php
   $join = (new Join('categories', 'products.category_id', '=', 'categories.id'))
       ->appendCondition(new Condition('categories.is_active', '=', true))
       ->appendCondition(new Condition('categories.type', '=', 'main'));
   ```

`apply(Builder $builder): void` // todo check if remove internal methods.

- Applies the join to the query builder
- Internal method used by the package

   ```php
   $join->apply($queryBuilder);
   ```

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\Join;
use RedaLabs\LaravelFilters\Filters\Conditions\Concretes\Condition;

// Basic join.
$join = new Join(
    'categories',
    'products.category_id',
    '=',
    'categories.id'
);

// Join with additional conditions.
$join = (new Join(
    'categories',
    'products.category_id',
    '=',
    'categories.id',
    'left'
))->appendCondition(
    new Condition('categories.is_active', '=', true)
);

// Join with custom name.
$join = new Join(
    'categories',
    'products.category_id',
    '=',
    'categories.id',
    'inner',
    'product_categories'
);

// Chaining multiple conditions.
$join = (new Join('categories', 'products.category_id', '=', 'categories.id'))
    ->appendCondition(new Condition('categories.is_active', '=', true))
    ->appendCondition(new Condition('categories.type', '=', 'main'))
    ->appendCondition(new Condition('categories.created_at', '>', '2024-01-01'));
```

### ClosureJoin

**Purpose**: Creates a join using a closure, allowing for more complex join conditions and logic. Useful when you need
to build dynamic or complex join conditions that can't be expressed with simple column comparisons.

#### Parameters:

- `string $table`: The table to join with
- `Closure $closure`: A closure that receives the join query builder and defines the join conditions
- `string $type`: The type of join (inner, left, right). Defaults to 'inner'
- `string|null $name`: Optional name for the join. If not provided, the table name is used

**Public Methods**:

1. `apply(Builder $builder): void`

- Applies the join to the query builder
- Internal method used by the package

   ```php
   $join->apply($queryBuilder);
   ```

**Example**:

```php
use RedaLabs\LaravelFilters\Filters\Joins\Concretes\ClosureJoin;

// Complex join with multiple conditions
$join = new ClosureJoin(
    'orders',
    function ($query) {
        $query->on('users.id', '=', 'orders.user_id')
              ->where('orders.status', '=', 'completed')
              ->where('orders.created_at', '>', now()->subDays(30));
    },
    'left'
);

// Join with custom name and type
$join = new ClosureJoin(
    'user_preferences',
    function ($query) {
        $query->on('users.id', '=', 'user_preferences.user_id')
              ->where('user_preferences.is_active', '=', true);
    },
    'left',
    'active_preferences'
);

// Complex join with multiple conditions and subqueries
$join = new ClosureJoin(
    'order_summary',
    function ($query) {
        $query->on('users.id', '=', 'order_summary.user_id')
              ->where('order_summary.total_orders', '>', function ($subquery) {
                  $subquery->selectRaw('AVG(total_orders)')
                          ->from('order_summary');
              })
              ->whereExists(function ($subquery) {
                  $subquery->select('id')
                          ->from('recent_orders')
                          ->whereColumn('user_id', 'users.id')
                          ->where('created_at', '>', now()->subDays(7));
              });
    }
);
```

> **Note**
>
> An `InvalidJoinTypeException` will be thrown if an unsupported join type is provided.

## Best Practices for Joins

**Join Naming and Execution Order**

When working with joins, it's important to assign clear and descriptive names—especially when joining the same table
multiple times or referencing joins later in your logic.

**Named Joins**: If a join with the same name already exists, it will be overwritten.

**Execution Order**: You can control the order in which joins are applied using the `appendJoin(BaseJoin $join, int $
order = 100)` method. Joins with lower order values are executed first.

Using named and ordered joins helps maintain predictable and maintainable query structures, particularly in complex
filtering scenarios.

## Sorts

The Laravel Filters package provides two types of sorts to help you order your query results.

### Sort

**Purpose**: Creates a standard `ORDER BY` clause for sorting query results. This is the most commonly used sort type,
allowing you to sort by a specific column in ascending or descending order.

#### Parameters:

- `string $field`: The column name to sort by
- `string $direction`: The sort direction ('asc' or 'desc'). Defaults to 'asc'.

**Available Methods**:

`apply(Builder $builder): void`

- Applies the sort to the query builder
- Internal method used by the package
   ```php
   $sort->apply($queryBuilder);
   ```

**Example**:

```php
use RedaLabs\LaravelFilters\Sorts\Concretes\Sort;

// Sort by name in ascending order
$sort = new Sort('name', 'asc');

// Sort by created_at in descending order
$sort = new Sort('created_at', 'desc');

// Sort by multiple fields
$sorts = [
    new Sort('status', 'asc'),
    new Sort('created_at', 'desc')
];
```

### RawSort

**Purpose**: Creates a raw `ORDER BY` clause for complex sorting requirements. Useful when you need to use SQL
expressions, functions, or complex sorting logic that can't be achieved with simple column sorting.

#### Parameters:

- `string $expression`: The raw SQL expression for sorting
- `array $bindings`: Array of values to bind to the expression. Defaults to empty array

**Available Methods**:

`apply(Builder $builder): void`

- Applies the raw sort to the query builder
- Internal method used by the package
   ```php
   $sort->apply($queryBuilder);
   ```

**Example**:

```php
use RedaLabs\LaravelFilters\Sorts\Concretes\RawSort;

// Sort by a calculated field
$sort = new RawSort('(price * quantity) DESC');

// Sort using a SQL function
$sort = new RawSort('LENGTH(name) ASC');

// Sort with bindings
$sort = new RawSort('FIELD(status, ?, ?, ?)', ['active', 'pending', 'inactive']);

// Complex sorting with multiple conditions
$sort = new RawSort('
    CASE 
        WHEN status = ? THEN 1
        WHEN status = ? THEN 2
        ELSE 3
    END ASC,
    created_at DESC
', ['active', 'pending']);
```

## Best Practices for Sorts

**Raw Sorts Usage**:

- Custom SQL expressions
- Database functions in sorting
- Complex conditional sorting
- Multiple column sorting with custom logic
- 🔒 Always use parameter binding with `RawSort`

**Multiple Sorts**:

- Order sorts by priority
- Consider the impact of each sort on performance
- Use appropriate indexes for multiple column sorts

---

## License

MIT
 