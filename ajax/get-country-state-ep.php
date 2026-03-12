<?php
namespace Phppot;

use Phppot\CountryState;
if (! empty($_POST["country_id"])) {
    
    $countryId = $_POST["country_id"];
    require_once __DIR__ . '/../Model/CountryState.php';
    $countryState = new CountryState();
    $stateResult = $countryState->getStateByCountrId($countryId);
?>

<option value="" disabled>Select User Role</option>

<?php
    foreach ($stateResult as $state) {
    ?>
<option value="<?php echo $state["role_id"]; ?>"><?php echo $state["systemrole"]; ?></option>
<?php
    }
}


?>