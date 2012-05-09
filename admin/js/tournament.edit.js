function getCurrentURL() {
    var tournamentId = $("#tournId").val();
    return window.location.pathname + "?action=edittournament" + "&tourn=" + tournamentId;
}

function submitAddPlayer() {
    var newUserId = $("#listOfUsers").val();
    window.location.replace(getCurrentURL() + "&addplayer=" + newUserId);
}

function submitGenerateTours() {
    var winnersCount = $("#winnersCount").val();
    var toursType = $("#toursType").val();
    window.location.replace(getCurrentURL() + "&generatetours=" + winnersCount + "&type=" + toursType);

}

function submitCloseTour() {
    var currentTourIndex = $("#tourIndex").val();
    window.location.replace(getCurrentURL() + "&closetour=" + currentTourIndex);
}

function setAvailabilityForSwissSystem() {
    var participantsCount = $("#players tr").length - 1;
    if (participantsCount % 2 == 1) {
        $("#generateTours").attr('disabled', 'disabled');
    } else {
        $("#generateTours").removeAttr('disabled', 'disabled');
    }
}

function submitAddGame(tourId) {
    var firstParticipantId = $("#firstParticipant").val();
    var secondParticipantId = $("#secondParticipant").val();
    if (!firstParticipantId || !secondParticipantId || firstParticipantId == secondParticipantId) {
        return;
    }
    window.location.replace(getCurrentURL() + "&addgame=" + tourId + "-" +firstParticipantId + "-" + secondParticipantId);
}

$(document).ready(function () {
    $("#listOfUsers").live('change', function () {
        submitAddPlayer()
    });

    $("#generateTours").live('click', function () {
        submitGenerateTours();
    });

    $("#closeTour").live('click', function () {
        submitCloseTour();
    });

    $("#toursType").live('change', function () {
        if ($("#toursType").val() == 0) {
            setAvailabilityForSwissSystem();
        }
        if ($("#toursType").val() == 1) {
            $("#generateTours").removeAttr('disabled', 'disabled');
        }
    });

    if ($("#toursType").val() == 0) {
        setAvailabilityForSwissSystem();
    }

    $(".addMicroMatchLink").live('click', function() {
        $(this).addClass("selected").parent().append($("#remarkPopup"));
        $(".pop").slideFadeToggle(function() {
            $("#remark").focus();
        });
        return false;
    });

    $(".closeMicroMatchPopup").live('click', function() {
        $(".pop").slideFadeToggle(function() {
            $(".addMicroMatchLink").removeClass("selected");
        });
        return false;
    });

    $("#addGame").live('click', function () {
        var tourId = $(this).parents('.addMatchDiv').siblings('.tourId').val();
        submitAddGame(tourId);
        return false;
    })
});

$.fn.slideFadeToggle = function(easing, callback) {
    return this.animate({ opacity: 'toggle', height: 'toggle' }, "fast", easing, callback);
};
