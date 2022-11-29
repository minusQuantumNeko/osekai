<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/global/php/functions.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
if(isset($_POST['bGetComments'])) {
    $colComments = array();
    if(isset($_POST['strMedalID'])) {
        if(isset($_SESSION['osu']['id'])) {
            $colComments = Database::execSelect("SELECT Comments.ID " .
                ", Comments.PostText " .
                ", Comments.UserID " . 
                ", Comments.PostDate " .
                ", Comments.ParentCommenter " .
                ", Coalesce(Comments.ParentComment, 0) AS Parent " .
                ", Comments.Username " .
                ", Comments.AvatarURL " .
                ", Comments.MedalID " .
                ", GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups" .
                ", (SELECT Votes.Vote FROM Votes WHERE Votes.UserID = ? AND Votes.ObjectID = Comments.ID AND Votes.Type = 1) AS HasVoted " .
                ", SUM(Votes.Vote) AS VoteSum "  .
            "FROM Comments " . 
            "LEFT JOIN Votes ON Votes.ObjectID = Comments.ID AND Votes.Type = 1 " . 
            "LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID " . 
            "WHERE MedalID = ? " . 
            "GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.MedalID, Comments.ParentComment", "is", array($_SESSION['osu']['id'], $_POST['strMedalID']));
        } else {
            $colComments = Database::execSelect("SELECT Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Coalesce(Comments.ParentComment, 0) AS Parent, Comments.Username, Comments.AvatarURL, Comments.MedalID, GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups, SUM(Votes.Vote) AS VoteSum FROM Comments LEFT JOIN Votes ON Votes.ObjectID = Comments.ID AND Votes.Type = 1 LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID WHERE MedalID = ? GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.MedalID, Comments.ParentComment", "s", array($_POST['strMedalID']));
        }
    } elseif(isset($_POST['nVersionId'])) {
        if(isset($_SESSION['osu']['id'])) {
            $colComments = Database::execSelect("SELECT Comments.ID " .
                ", Comments.PostText " .
                ", Comments.UserID " . 
                ", Comments.PostDate " .
                ", Comments.ParentCommenter " .
                ", Coalesce(Comments.ParentComment, 0) AS Parent " .
                ", Comments.Username " .
                ", Comments.AvatarURL " .
                ", Comments.VersionId AS MedalID " .
                ", GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups" .
                ", (SELECT Votes.Vote FROM Votes WHERE Votes.UserID = ? AND Votes.ObjectID = Comments.ID AND Votes.Type = 3) AS HasVoted " .
                ", SUM(Votes.Vote) AS VoteSum "  .
            "FROM Comments " . 
            "LEFT JOIN Votes ON Votes.ObjectID = Comments.ID AND Votes.Type = 3 " . 
            "LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID " . 
            "WHERE VersionId = ? " . 
            "GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.VersionId, Comments.ParentComment", "ii", array($_SESSION['osu']['id'], $_POST['nVersionId']));
        } else {
            $colComments = Database::execSelect("SELECT Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Coalesce(Comments.ParentComment, 0) AS Parent, Comments.Username, Comments.AvatarURL, Comments.VersionId AS MedalID, GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups, SUM(Votes.Vote) AS VoteSum FROM Comments LEFT JOIN Votes ON Votes.ObjectID = Comments.ID AND Votes.Type = 3 LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID WHERE VersionId = ? GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.VersionId, GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups,Comments.ParentComment", "i", array($_POST['nVersionId']));
        }
    } elseif(isset($_POST['nProfileId'])) {
        if(isset($_SESSION['osu']['id'])) {
            $colComments = Database::execSelect("SELECT Comments.ID " .
                ", Comments.PostText " .
                ", Comments.UserID " . 
                ", Comments.PostDate " .
                ", Comments.ParentCommenter " .
                ", Coalesce(Comments.ParentComment, 0) AS Parent " .
                ", Comments.Username " .
                ", Comments.AvatarURL " .
                ", Comments.ProfileID AS MedalID " .
                ", GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups" .
                ", (SELECT Votes.Vote FROM Votes WHERE Votes.UserID = ? AND Votes.ObjectID = Comments.ID AND Votes.Type = 4) AS HasVoted " .
                ", SUM(Votes.Vote) AS VoteSum "  .
            "FROM Comments " . 
            "LEFT JOIN Votes ON Votes.ObjectID = Comments.ID AND Votes.Type = 4 " . 
            "LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID " . 
            "WHERE ProfileID = ? " . 
            "GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.ProfileID, Comments.ParentComment", "ii", array($_SESSION['osu']['id'], $_POST['nProfileId']));
        } else {
            $colComments = Database::execSelect("SELECT 
            Comments.ID, 
            Comments.PostText, 
            Comments.UserID, 
            Comments.PostDate, 
            Comments.ParentCommenter, 
            Coalesce(Comments.ParentComment, 0) AS Parent, 
            Comments.Username, Comments.AvatarURL, 
            Comments.ProfileID AS MedalID, 
            GROUP_CONCAT(GroupAssignments.GroupId SEPARATOR ',') as Groups,
            SUM(Votes.Vote) 
            AS VoteSum FROM Comments LEFT JOIN Votes ON 
            Votes.ObjectID = Comments.ID AND 
            Votes.Type = 4 
            LEFT JOIN GroupAssignments ON GroupAssignments.UserId = Comments.UserID WHERE ProfileID = ? 
            GROUP BY Comments.ID, Comments.PostText, Comments.UserID, Comments.PostDate, Comments.ParentCommenter, Comments.ProfileID, Comments.ParentComment", "i", array($_POST['nProfileId']));
        }
    }
    echo json_encode($colComments);
}

if(isset($_POST['strComment'])) {
    if(isRestricted()) return;
    if(isset($_SESSION['osu']['id'])) {
        if(isset($_POST['strCommentMedalID'])) {
            if(isset($_POST['nParentComment'])) {
                Database::execOperation("INSERT INTO Comments (PostText, MedalID, Username, UserID, AvatarURL, ParentComment, ParentCommenter, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", "sssisis", array($_POST['strComment'], $_POST['strCommentMedalID'], $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url'], $_POST['nParentComment'], $_POST['strParentCommenter']));
                echo json_encode("Success!");
            } else {
                Database::execOperation("INSERT INTO Comments (PostText, MedalID, Username, UserID, AvatarURL, PostDate) VALUES (?, ?, ?, ?, ?, NOW())", "sssis", array($_POST['strComment'], $_POST['strCommentMedalID'], $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url']));
                echo json_encode("Success!");
            }
        } elseif(isset($_POST['nVersionId'])) {
            if(isset($_POST['nParentComment'])) {
                Database::execOperation("INSERT INTO Comments (PostText, VersionId, Username, UserID, AvatarURL, ParentComment, ParentCommenter, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", "sisisis", array($_POST['strComment'], intval($_POST['nVersionId']), $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url'], $_POST['nParentComment'], $_POST['strParentCommenter']));
                echo json_encode("Success!");
            } else {
                Database::execOperation("INSERT INTO Comments (PostText, VersionId, Username, UserID, AvatarURL, PostDate) VALUES (?, ?, ?, ?, ?, NOW())", "sisis", array($_POST['strComment'], intval($_POST['nVersionId']), $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url']));
                echo json_encode("Success!");
            }
        } elseif(isset($_POST['nProfileId'])) {
            if(isset($_POST['nParentComment'])) {
                Database::execOperation("INSERT INTO Comments (PostText, ProfileID, Username, UserID, AvatarURL, ParentComment, ParentCommenter, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", "sisisis", array($_POST['strComment'], intval($_POST['nProfileId']), $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url'], $_POST['nParentComment'], $_POST['strParentCommenter']));
                echo json_encode("Success!");
            } else {
                Database::execOperation("INSERT INTO Comments (PostText, ProfileID, Username, UserID, AvatarURL, PostDate) VALUES (?, ?, ?, ?, ?, NOW())", "sisis", array($_POST['strComment'], intval($_POST['nProfileId']), $_SESSION['osu']['username'], $_SESSION['osu']['id'], $_SESSION['osu']['avatar_url']));
                echo json_encode("Success!");
            }
        }
    }
}

if(isset($_POST['strUserID'])) {
    echo json_encode(getuser($_POST['strUserID']));
}

if(isset($_POST['nObject'])) {
    if(isRestricted()) return;
    if(isset($_SESSION['osu']['id'])) {
        $hasVoted = array();
        $hasVoted = Database::execSelect("SELECT Vote AS HasVoted FROM Votes Where UserID = ? AND ObjectID = ? AND Type = ? UNION SELECT 0 AS HasVoted LIMIT 1", "iii", array($_SESSION['osu']['id'], $_POST['nObject'], $_POST['nType']));

        if ($hasVoted[0]['HasVoted'] == 1) {
            Database::execOperation("DELETE FROM Votes WHERE UserID = ? AND ObjectID = ? AND Type = ?", "iii", array($_SESSION['osu']['id'], $_POST['nObject'], $_POST['nType']));
        } else {
            Database::execOperation("INSERT INTO Votes (UserID, ObjectID, Vote, Type) VALUES (?, ?, 1, ?)", "iii", array($_SESSION['osu']['id'], $_POST['nObject'], $_POST['nType']));
        }
        
        echo json_encode($hasVoted);
    }
}

if(isset($_POST['nCommentDeletion'])) {
    if(isRestricted()) return;
    if(isset($_SESSION['osu']['id'])) {
        if($_SESSION['role']['rights'] > 0) {
            $comment_data = Database::execSelect("SELECT * FROM Comments WHERE ID = ?", "i", array($_POST['nCommentDeletion']))[0];
            $on = "unknown";
            if($comment_data['MedalID'] != null) {
                $on = "Medal " . Database::execSelect("SELECT name FROM Medals WHERE medalid = ?", "i", [$comment_data['MedalID']])[0]['name'];
            }
            if($comment_data['ProfileID'] != null) {
                $on = "User " . $comment_data['UserID'];
            }
            if($comment_data['VersionID'] != null) {
                $on = "Version " . Database::execSelect("SELECT Name FROM SnapshotsAzeliaVersions WHERE Id = ?", "i", [$comment_data['VersionID']])[0]['Name'];
            }
            Logging::PutLog("<h1>Deleted comment <strong>#{$_POST['nCommentDeletion']}</strong> by <strong>{$comment_data['Username']}</strong> on {$on}</h1><p>{$comment_data['PostText']}</p>");
            // END LOGGING
            Database::execOperation("DELETE FROM Comments WHERE ID = ?", "i", array($_POST['nCommentDeletion']));
        } else {
            Database::execOperation("DELETE FROM Comments WHERE ID = ? AND UserID = ?", "ii", array($_POST['nCommentDeletion'], $_SESSION['osu']['id']));
        }
        echo json_encode("Success!");
    }
}
?>