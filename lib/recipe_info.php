<?php

require_once 'user.php';

class recipeInfo
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function selectPreparations($recipe_id)
    {
        $preparations = array();
        $sql = "SELECT * FROM recipe_info WHERE recipe_id = $recipe_id AND record_type='P'";
        $result = $this->connection->query($sql);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $preparations[] = $row;
        }
        return $preparations;
    }

    public function selectRatings($recipe_id)
    {
        $ratings = array();
        $sql = "SELECT * FROM recipe_info WHERE recipe_id = $recipe_id AND record_type='R'";
        $result = $this->connection->query($sql);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $ratings[] = $row;
        }
        return $ratings;
    }

    public function calculateAverageRating($recipe_id)
    {
        $ratings = array();
        $ratings = $this->selectRatings($recipe_id);

        $ratingValues = array();
        $averageRating = 0;
        if (count($ratings) > 0) {
            foreach ($ratings as $rating) {
                $ratingValues[] = $rating['nummeric_field'];
            }
            $averageRating = array_sum($ratingValues) / count($ratingValues);
        }
        return $averageRating;
    }

    public function isFavorite($recipe_id, $user_id)
    {
        $sql = "SELECT * FROM recipe_info WHERE recipe_id = $recipe_id AND `user_id` = $user_id AND record_type='F' ";
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            $isFavorite = true;
        } else {
            $isFavorite = false;
        }
        return $isFavorite;
    }

    public function addFavorite($recipe_id, $user_id)
    {
        $sql = "INSERT INTO recipe_info(id, record_type, recipe_id, user_id, date, nummeric_field, text_field) VALUES(NULL, 'F', '$recipe_id', '$user_id', current_timestamp(), NULL, NULL)";
        if (mysqli_query($this->connection, $sql)) {
            echo "Added to favorites";
        } else {
            echo "Sorry, something went wrong. Try agian later";
        }
    }
    public function deleteFavorite($recipe_id, $user_id)
    {
        $sql = "DELETE FROM recipe_info WHERE recipe_id = $recipe_id AND user_id=$user_id";
        if (mysqli_query($this->connection, $sql)) {
            echo "Deleted from favorites";
        } else {
            echo "Sorry, something went wrong. Try again later";
        }
    }

    public function selectComments($recipe_id)
    {
        $comments = array();
        $sql = "SELECT * FROM recipe_info WHERE recipe_id = $recipe_id AND record_type='C'";
        $result = $this->connection->query($sql);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $comments[] = $row;
        }
        $updatedComments = $this->selectCommentUsernames($comments);
        return $updatedComments;
    }

    public function selectCommentUsernames($comments)
    {
        $userModel = new user($this->connection);
        $updatedComments = array();
        foreach ($comments as $comment) {
            $user = $userModel->selectUserById($comment['user_id']);
            $comment['user'] = $user;
            $updatedComments[] = $comment;
        }
        return $updatedComments;
    }

    public function updateRating($recipe_id, $rating, $user_id)
    {
        $sql = "SELECT * FROM recipe_info WHERE recipe_id = $recipe_id AND record_type='R'and user_id = $user_id";
        $result = $this->connection->query($sql);
        if ($result->num_rows < 1) {
            $sql = "INSERT INTO recipe_info(id, record_type, recipe_id, user_id, date, nummeric_field, text_field) VALUES(NULL, 'R', '$recipe_id', '$user_id', current_timestamp(), $rating, NULL)";
            if (mysqli_query($this->connection, $sql)) {
                echo "Added rating";
            } else {
                echo "Sorry, something went wrong. Try again later";
            }
        } else {
            $previous_rating = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $sql = "UPDATE `recipe_info` SET `nummeric_field` = '$rating' WHERE id = $previous_rating[id]";
            if (mysqli_query($this->connection, $sql)) {
                echo "updated rating";
            } else {
                echo "Sorry, something went wrong. Try again later";
            }
        }
    }
}
