use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dosen\PertemuanController;

Route::get('/pertemuan/{id_jadwal}', [PertemuanController::class, 'apiIndex']);
