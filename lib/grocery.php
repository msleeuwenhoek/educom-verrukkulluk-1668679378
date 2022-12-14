<?php
require_once 'user.php';
require_once 'article.php';
require_once "recipe.php";

class grocery
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function addGrocery($user_id, $article_id, $amount)
    {
        $sql = "INSERT INTO grocery (user_id, article_id, amount) VALUES ('$user_id', '$article_id', '$amount')";
        if (mysqli_query($this->connection, $sql)) {
            echo "Added to grocery list";
        } else {
            echo "Sorry, something went wrong. Try agian later";
        }
    }

    public function deleteGrocery($user_id, $article_id)
    {
        $sql = "DELETE FROM grocery WHERE  user_id=$user_id AND article_id = $article_id";
        if (mysqli_query($this->connection, $sql)) {
            echo "Deleted from groceries";
        } else {
            echo "Sorry, something went wrong. Try agian later";
        }
    }

    public function selectGroceries($user_id)
    {
        $groceries = array();
        $sql = "SELECT * FROM grocery WHERE user_id=$user_id";
        $result = $this->connection->query($sql);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $groceries[] = $row;
        }

        // Retrieving associated data
        $user = $this->selectUser($user_id);
        $updatedGroceries = $this->selectGroceryArticles($groceries);
        $totalPrice = $this->calculateTotalPrice($updatedGroceries);

        // Returning all associated data
        return array('groceries' => $updatedGroceries, 'user' => $user, 'total_price' => $totalPrice);
    }

    private function selectUser($user_id)
    {
        $userModel = new user($this->connection);
        $user = $userModel->selectUserById($user_id);
        return $user;
    }


    private function selectGroceryArticles($groceries)
    {
        $articleModel = new article($this->connection);
        $updatedGroceries = array();
        foreach ($groceries as $grocery) {
            $article = $articleModel->selectArticleById($grocery['article_id']);
            $grocery['article'] = $article;
            $updatedGroceries[] = $grocery;
        }
        return $updatedGroceries;
    }

    private function calculateTotalPrice($groceries)
    {
        $groceryPrices = array();
        foreach ($groceries as $grocery) {
            $groceryPrice = $grocery['article']['price']  * $grocery['amount'];
            $groceryPrices[] = $groceryPrice;
        }
        $totalPrice = round(array_sum($groceryPrices), 2);
        return $totalPrice;
    }

    public function addRecipeGroceries($recipe_id, $user_id)
    {
        $recipeModel = new recipe($this->connection);
        $recipe = $recipeModel->selectRecipe($recipe_id);
        foreach ($recipe['ingredients'] as $ingredient) {
            $article_id = $ingredient['article']['id'];
            $amount = $ingredient['amount'];



            $sql = "SELECT * FROM grocery WHERE article_id = $article_id AND  user_id = $user_id";
            $result = $this->connection->query($sql);
            if ($result->num_rows < 1) {
                $sql = "INSERT INTO grocery(id, user_id, article_id, amount) VALUES(NULL, $user_id, $article_id, $amount)";
                mysqli_query($this->connection, $sql);
            } else {
                $current_grocery = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $sql = "UPDATE `grocery` SET `amount` = amount + 1 WHERE id = $current_grocery[id]";
                mysqli_query($this->connection, $sql);
            }
        }
    }
}
