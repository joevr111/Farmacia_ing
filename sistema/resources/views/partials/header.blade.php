<header class="app-header bg-white border-bottom shadow-sm d-flex align-items-center px-3 px-lg-4 gap-3">

      <button class="btn btn-sm btn-outline-secondary border-0 sidebar-toggle-btn"
              data-sidebar-toggle
              aria-label="Alternar menú lateral">
        <i class="bi bi-list fs-5"></i>
      </button>

      <h1 class="h5 fw-bold mb-0 me-auto">@yield('name_section')</h1>

      <div class="d-flex align-items-center gap-2">

        @php
            $badge = $notifLowStockCount + $notifUnread;
            $icons = [
                'ingreso'      => ['bi-box-arrow-in-down', 'text-success'],
                'salida'       => ['bi-cart-check', 'text-primary'],
                'anulacion'    => ['bi-x-circle', 'text-danger'],
                'recuperacion' => ['bi-arrow-counterclockwise', 'text-success'],
            ];
        @endphp

        <div class="dropdown">
          <button class="btn btn-sm btn-outline-secondary border-0 position-relative rounded-3"
                  type="button"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                  aria-label="Notificaciones">
            <i class="bi bi-bell fs-5"></i>
            @if ($badge > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary"
                    style="font-size:.58rem;padding:.28em .45em">{{ $badge > 9 ? '9+' : $badge }}</span>
            @endif
          </button>

          <ul class="dropdown-menu dropdown-menu-end shadow border" style="min-width:300px;max-height:70vh;overflow-y:auto">

            {{-- Stock bajo / sin stock --}}
            @if ($notifLowStock->isNotEmpty())
              <li><h6 class="dropdown-header">Alertas de stock ({{ $notifLowStockCount }})</h6></li>
              @foreach ($notifLowStock as $med)
                <li>
                  <a class="dropdown-item py-2" href="{{ route('medicines.index', ['low_stock' => 1]) }}">
                    <div class="d-flex align-items-start gap-2">
                      <i class="bi bi-exclamation-triangle {{ $med->stock == 0 ? 'text-danger' : 'text-warning' }} mt-1 flex-shrink-0"></i>
                      <div>
                        <div class="small fw-medium">
                          {{ $med->stock == 0 ? 'Sin stock' : 'Stock bajo' }}: {{ $med->name }}
                        </div>
                        <div class="text-secondary" style="font-size:.72rem">
                          {{ $med->stock == 0 ? 'No quedan unidades' : 'Quedan ' . $med->stock . ' unidades' }}
                        </div>
                      </div>
                    </div>
                  </a>
                </li>
              @endforeach
              <li><hr class="dropdown-divider"></li>
            @endif

            {{-- Ingresos y salidas --}}
            <li><h6 class="dropdown-header">Movimientos recientes</h6></li>
            @forelse ($notifActivities as $act)
              @php [$icon, $color] = $icons[$act->type] ?? ['bi-bell', 'text-secondary']; @endphp
              <li>
                <div class="dropdown-item py-2 {{ $act->is_read ? 'text-muted' : '' }}" style="white-space:normal">
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi {{ $icon }} {{ $color }} mt-1 flex-shrink-0"></i>
                    <div>
                      <div class="small fw-medium">{{ $act->message }}</div>
                      <div class="text-secondary" style="font-size:.72rem">
                        {{ $act->detail }} · {{ $act->created_at->locale('es')->diffForHumans() }}
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            @empty
              <li><span class="dropdown-item-text small text-muted">Sin movimientos todavía</span></li>
            @endforelse

            <li><hr class="dropdown-divider"></li>
            <li class="px-3 pb-1">
              @if ($notifUnread > 0)
                <form action="{{ route('notifications.read') }}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-light w-100 text-primary fw-medium">
                    Marcar como leídas
                  </button>
                </form>
              @endif
            </li>
          </ul>
        </div>

      </div>
    </header>