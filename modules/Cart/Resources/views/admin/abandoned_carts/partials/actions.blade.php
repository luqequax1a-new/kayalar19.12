<div style="display: flex; gap: 4px;">
    <a href="{{ route('admin.abandoned_carts.show', $cart->id) }}" 
       class="btn-icon" 
       title="Detayları Görüntüle"
       style="width: 28px; height: 28px; border-radius: 4px; display: flex; align-items: center; justify-content: center; border: 1px solid #d9d9d9; background: #fff; color: rgba(0, 0, 0, 0.45); transition: all 0.2s; text-decoration: none;">
        <i class="fa fa-eye"></i>
    </a>

    @if($cart->customer_email && !$cart->is_recovered)
        <button type="button"
                class="btn-icon send-reminder-btn" 
                data-cart-id="{{ $cart->id }}"
                title="Manuel Hatırlatma Gönder"
                style="width: 28px; height: 28px; border-radius: 4px; display: flex; align-items: center; justify-content: center; border: 1px solid #d9d9d9; background: #fff; color: rgba(0, 0, 0, 0.45); cursor: pointer; transition: all 0.2s;">
            <i class="fa fa-envelope"></i>
        </button>
    @endif
</div>
