<script type="text/javascript" src="js/tournament.edit.js"></script>

<?php


if (!defined('IS_VALID_PHPMYFAQ')) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

if ($permission['edittourn']) {
    $tournament_id = PMF_Filter::filterInput(INPUT_GET, 'tourn', FILTER_VALIDATE_INT, 0);


    $add_player_id = PMF_Filter::filterInput(INPUT_GET, 'addplayer', FILTER_VALIDATE_INT, 0);
    $remove_player_id = PMF_Filter::filterInput(INPUT_GET, 'removeplayer', FILTER_VALIDATE_INT, 0);
    $winners_count = PMF_Filter::filterInput(INPUT_GET, 'generatetours', FILTER_VALIDATE_INT, 0);
    $tour_id_to_close = PMF_Filter::filterInput(INPUT_GET, 'closetour', FILTER_VALIDATE_INT, 0);

    if ($add_player_id != 0) {
        PMF_TournamentService::addPlayerToTournament($tournament_id, $add_player_id);
    }

    if ($remove_player_id != 0) {
        PMF_TournamentService::removePlayerFromTournament($tournament_id, $remove_player_id);
    }

    if ($tour_id_to_close != 0) {
        PMF_TournamentService::closeTourAndGenerateNext($tournament_id, $tour_id_to_close);
    }

    $tournament = PMF_TournamentService::getById($tournament_id);
    $tournament_started = $tournament->started != 0;
    if ($winners_count && !$tournament_started) {
        $tours_type = $_GET['type'];
        PMF_TournamentService::generateTours($tournament_id, $winners_count, $tours_type);
        $tournament_started = true;
    }

    $first_new_score = $_REQUEST['first'];
    $second_new_score = $_REQUEST['second'];
    if (isset($first_new_score) && isset($second_new_score)) {
        $game_id = $_REQUEST['game'];
        PMF_TournamentService::updateGameScore($game_id, $first_new_score, $second_new_score);
        PMF_TournamentService::updateStandings($tournament_id);
    }
    ?>

<header>
    <h2><?php print $PMF_LANG['ad_tournedit_header'] . '&nbsp' . $tournament->name; ?></h2>
</header>

<form action="?action=updatetournament" method="post">
    <input id="tournId" type="hidden" name="id" value="<?php print $tournament_id; ?>" />

    <div class="inputs-block">
        <span class="input-left">
            <label class="control-label" for="name"><?php print $PMF_LANG['ad_tournedit_title']; ?>:</label>
        </span>
        <span class="input-text">
            <input type="text" id="name" name="name" value="<?php print $tournament->name; ?>" />
        </span>
    </div>

    <div class="inputs-block">
        <span class="input-left">
            <label class="control-label" for="description"><?php print $PMF_LANG['ad_tournedit_desc']; ?>:</label>
        </span>
        <span class="input-text">
            <textarea id="description" name="description" rows="3" cols="80"><?php print $tournament->description ?></textarea>
        </span>
    </div>

    <div class="inputs-block">
        <span class="input-left">
            <label class="control-label" for="deleted"><?php print $PMF_LANG['ad_tournedit_deleted']; ?>:</label>
        </span>
        <span class="input-text">
            <?php
            if ($tournament->deleted) {
                print '<input id="deleted" name="deleted" type="checkbox" checked="checked"/>';
            } else {
                print '<input id="deleted" name="deleted" type="checkbox" />';
            }
            ?>
        </span>
    </div>
    <?php
        if ($tournament->started) {
    ?>
            <div class="inputs-block">
                <span class="input-left">
                    <label class="control-label"><?php print $PMF_LANG['ad_tournedit_generate_tours_type']; ?>:</label>
                </span>
                <span class="input-text">
                    <?php
                        if ($tournament->tours_type == 0) {
                            print $PMF_LANG['ad_tournedit_generate_tours_swiss'];
                        } else if ($tournament->tours_type == 1) {
                            print $PMF_LANG['ad_tournedit_generate_tours_round'];
                        }
                    ?>
                </span>
            </div>
    <?php
        }
    ?>
    <div class="inputs-block">
        <span class="input-left">
            <label class="control-label" for="pointsSystem"><?php print $PMF_LANG['ad_tournedit_points_system']; ?>:</label>
        </span>
        <span class="input-text">
            <select id="pointsSystem" name="pointsSystem">
                <?php
                    if ($tournament->points_system == '3-1-0') {
                        printf('<option value="3-1-0" selected="selected">%s</option>', '3 - 1 - 0');
                        printf('<option value="1-0.5-0">%s</option>', '1 - 0.5 - 0');
                    } else {
                        printf('<option value="3-1-0">%s</option>', '3 - 1 - 0');
                        printf('<option value="1-0.5-0" selected="selected">%s</option>', '1 - 0.5 - 0');
                    }
                ?>
            </select>
        </span>
    </div>

    <div class="form-actions">
        <input class="btn-primary" type="submit" name="submit" value="<?php print $PMF_LANG['ad_tournedit_submit']; ?>" />
    </div>
</form>
<div style="width: 75%;">
    <?php
    $players = PMF_Player::getAllPlayersForTournament($tournament_id);
    if (count($players) > 0) {
        require_once '../common/players.update.values.php';
        print '<table border="1"  width="100%">';
        printf("<th>%s</th>", "");
        printf("<th>%s</th>", $PMF_LANG['ad_player_last_name']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_first_name']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_country']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_birth_year']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_title']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_rating']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_category']);
        printf("<th>%s</th>", $PMF_LANG['ad_player_degree']);
        $i = 1;
        foreach ($players as $player) {
            print '<tr>';
            printf("<td>%d</td>", $i++);
            printf("<td>%s</td>", $player->last_name);
            printf("<td>%s</td>", $player->first_name);
            printf("<td style='text-align: center;'><img src='../images/countries/32/%s.png' title='%s'></td>", $player->country, $player->country_title);
            printf("<td>%s</td>", $player->birth_year);
            printf("<td>%s</td>", $player->title);
            printf("<td>%s</td>", $player->rating);
            printf("<td>%s</td>", $player->category);
            printf("<td>%s</td>", $player->degree);
            if (!$tournament_started) {
                print "<td>";
                printf('<a href="?action=edittournament&amp;tourn=%s&removeplayer=%s"><img src="images/delete.png" width="16" height="16" alt="%s" title="%s" border="0" /></a>&nbsp;',
                    $tournament_id,
                    $player->id,
                    $PMF_LANG['ad_categ_delete'],
                    $PMF_LANG['ad_categ_delete']
                );
                print "</td>";
            }

            print '</tr>';
        }
        print '</table>';
    }

    $not_in_tournament_players = PMF_Player::getAllPlayersThatNotInTournament($tournament_id);
    ?>
    <?php
    if (!$tournament_started) {
        ?>
        <div style="margin-top: 15px;">
            <?php print $PMF_LANG['ad_menu_add_player'] . ":"; ?>
            <select id="listOfUsers">
                <option></option>
                <?php
                foreach ($not_in_tournament_players as $player) {
                    printf("<option value='%d'>%s %s</option>", $player->id, $player->last_name, $player->first_name);
                }
                ?>
            </select>
            <?php
            if (count($players) >= 4) {
                ?>
                <label for="winnersCount" style="display: inline;"><?php print $PMF_LANG['ad_tournedit_generate_tours_winners_count'] . ":"; ?></label>
                <select id="winnersCount" style="width: 50px;">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
                <div>
                    <label for="toursType" style="display: inline;"><?php print $PMF_LANG['ad_tournedit_generate_tours_type'] . ":"; ?></label>
                    <select id="toursType" style="width: 250px;">
                        <option value="0"><?php print $PMF_LANG['ad_tournedit_generate_tours_swiss'] ?></option>
                        <option value="1"><?php print $PMF_LANG['ad_tournedit_generate_tours_round'] ?></option>
                    </select>
                </div>
                <?php
                printf("<input id='generateTours' type='button' value='%s' disabled='disabled' title='%s'/>",
                    $PMF_LANG['ad_tournedit_generate_tours'], $PMF_LANG['ad_tournedit_generate_tours_disabled']);
            }
            ?>
        </div>
        <?php
    } else {
        print '<section class="standings">';
        printf('<header><h3>%s</h3></header>', $PMF_LANG['ad_standings']);
        print PMF_TournamentRenderer::renderTournamentStandings($tournament_id, $PMF_LANG);
        print '</section>';
    }
    ?>

    <section class="tours">
        <?php
        $tours = PMF_TournamentService::getTours($tournament_id);

        foreach ($tours as $tour) {
            if (!$tour->finished) {
                print "<article class='tour current'>";
            } else {
                print "<article class='tour'>";
            }
            print "<header>";
            printf("<h3>%s %d</h3>", $PMF_LANG['tour'], $tour->tour_index);
            print "</header>";
            print "<table class='games' border='0'>";
            foreach ($tour->games as $game) {
                print "<tr>";
                printf("<td><img src='../images/countries/16/%s.png'/></td>", $game->first_country);
                printf("<td>%s</td>", $game->first_name);
                printf("<td><div style='padding-left: 10px;'>%s</div></td>", $game->first_participant_score);
                print "<td>:</td>";
                printf("<td><div style='padding-right: 10px;'>%s</div></td>", $game->second_participant_score);
                printf("<td><img src='../images/countries/16/%s.png'/></td>", $game->second_country);
                printf("<td>%s</td>", $game->second_name);

                print "<td><div style='padding-left: 15px'> ";
                printf('<a href="?action=editgame&amp;game=%s"><img src="images/edit.png" width="16" height="16" alt="%s" title="%s" border="0" /></a>&nbsp;',
                    $game->id,
                    $PMF_LANG['game_edit_score'],
                    $PMF_LANG['game_edit_score']
                );
                print "</div></td>";
                print "</tr>";
            }
            print "</table>";
            if (!$tour->finished) {
                printf("<input id='tourIndex' type='hidden' value='%s'/>", $tour->id);
                printf("<input id='closeTour' class='close-tour-button' type='submit' value='%s'/>", $PMF_LANG['ad_tour_close']);
            }
            print "</article>";
        }
        ?>
    </section>
</div>


<?php
} else {
    print $PMF_LANG['err_NotAuth'];
}
