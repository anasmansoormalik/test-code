<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DelayedOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class OrderController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {

    abort_if($request->user()->cannot('viewAny', Order::class), 403);

    //put user preferences in session
    $setting = auth()->user()->settings;
    if ($setting) {
      foreach (json_decode($setting, true) as $key => $s) {
        session()->put($key, $s);
      }
    }
    if (request()->has('navigation')) {
      session()->put('navigtionQuery', request()->getQueryString());
    }

    return view("order.list");
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, $order)
  {

    $order = Order::query()->where('org_order_id', $order)->firstOrFail();

    abort_if($request->user()->cannot('view', $order), 403);
    $delay = DelayedOrder::where('order_id', $order->id)->where('status', true)->first();
    $appointments = $order->appointment()->get();
    $communications = $order->communications()->latest()->get();
    if (session()->has('navigtionQuery')) {
      session()->forget('navigtionQuery');
    }
    return view('order.view', compact('order', 'appointments', 'communications', 'delay'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Order $order)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Order $order)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Order $order)
  {
    //
  }


  public function updateStatus(Request $request, Order $order)
  {
    $order->internal_status = $request->internal_status;
    $order->internal_details = $request->comment;
    $order->save();
    if ($order->internal_status == 2) {
      if ($request->appointment_id) {
        $appointment = Appointment::where('id', $request->appointment_id)->first();
        $appointment->appointment_at = $request->appointment_at;
        $appointment->via = $request->via;
        $appointment->status = $request->ap_status;
        $appointment->comment = $request->comment;
        if ($appointment->isDirty('status')) {
          if ($appointment->status == 1) {
            $appointment_status = 'open';
          } else if ($appointment->status == 2) {
            $appointment_status = 'completed';
          } else {
            $appointment_status = 'cancelled';
          }
          $order->audits()->create(['tags' => 'appointment-' . $appointment_status, 'event' => 'updated', 'old_values' => '[]', 'new_values' => json_encode($appointment)]);
        }
        $appointment->save();
      } else {
        $appointment = Appointment::create([
          'order_id' => $order->id,
          //'communication_id' =>
          'appointment_at' => $request->appointment_at,
          'via' => $request->via,
          'comment' => $request->comment,
          'status' => 1,
          'created_by' => auth()->id()
        ]);
        $order->audits()->create(['tags' => 'appointment-open', 'event' => 'updated', 'old_values' => '[]', 'new_values' => json_encode($appointment)]);
      }

    }
    return back()->withSuccess(__('order.statusUpdatedMsg'));
  }

  public function audit(Order $order, Audit $audit)
  {
    return view('audit.view_updated', compact('order', 'audit'));
  }

  public function downloadDocument(Order $order, Request $request)
  {
    $type = $request->type;
    if (!$type) {
      session()->flash('error', __('order.documentTypeNotProvided'));
      return back();
    }

    if ($type == 'final') {
      if ($order->internalDetail?->final_pdf) {
        $final_files = json_decode($order->internalDetail->final_pdf);
        if ($request->version) {
          return response()->download($final_files[$request->version - 1]->file);
        } else {
          return response()->download($final_files[count($final_files) - 1]['file']);
        }
      }
    } else if ($type == 'initial') {
      if ($order->internalDetail?->empty_pdf) {
        return response()->download($order->internalDetail->empty_pdf);
      }
    }
    session()->flash('error', __('order.undefinedDocTypeProvided'));
    return back();
  }


  public function updatePropertyStatus(Request $request, Order $order, $property, $p_id)
  {
    abort_if($request->user()->cannot('update', $order), 403);
    if (!method_exists($order, $property)) {
      return to_route('order.show', ['order' => $order->org_order_id])->with('error', __($property . ' relation not defined on model order'));
    }

    $property = $order->$property()->findOrFail($p_id);
    $property->update(['is_valid' => !$property->is_valid]);
    return to_route('order.show', ['order' => $order->org_order_id])->with('success', __('order.propertyUpdated'));
  }
}
