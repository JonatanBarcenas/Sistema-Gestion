@extends('layouts.app')

@section('title', isset($order) ? 'Editar Pedido' : 'Nuevo Pedido')

@section('content')
<div class="bg-white shadow-sm rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">{{ isset($order) ? 'Editar Pedido' : 'Nuevo Pedido' }}</h2>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>

        <form action="{{ isset($order) ? route('orders.update', $order) : route('orders.store') }}" method="POST" id="orderForm">
            @csrf
            @if(isset($order))
                @method('PUT')
            @endif

            <!-- Mostrar errores globales -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <h3 class="font-semibold">Por favor corrige los siguientes errores:</h3>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cliente -->
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select name="customer_id" id="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('customer_id') border-red-500 @enderror">
                        <option value="">Seleccione un cliente</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $order->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de Entrega -->
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700">Fecha de Entrega</label>
                    <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date', isset($order) ? $order->delivery_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('delivery_date') border-red-500 @enderror">
                    @error('delivery_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $order->status ?? '') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="in_progress" {{ old('status', $order->status ?? '') === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                        <option value="completed" {{ old('status', $order->status ?? '') === 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ old('status', $order->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notas -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('notes') border-red-500 @enderror">{{ old('notes', $order->notes ?? '') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Productos -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Productos</h3>
                <div id="products-container">
                    @if(isset($order) && $order->products->count() > 0)
                        @foreach($order->products as $product)
                            <div class="product-item grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Producto</label>
                                    <select name="products[{{ $loop->index }}][product_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccione un producto</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ $product->id == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                    <input type="number" name="products[{{ $loop->index }}][quantity]" value="{{ $product->pivot->quantity }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                                    <input type="number" name="products[{{ $loop->index }}][unit_price]" value="{{ $product->pivot->unit_price }}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" class="remove-product inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="product-item grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Producto</label>
                                <select name="products[0][product_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Seleccione un producto</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                                <input type="number" name="products[0][quantity]" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                                <input type="number" name="products[0][unit_price]" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-product inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <button type="button" id="add-product" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Agregar Producto
                    </button>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ isset($order) ? 'Actualizar' : 'Crear' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsContainer = document.getElementById('products-container');
    const addProductButton = document.getElementById('add-product');
    let productCount = productsContainer.children.length;

    // Agregar producto
    addProductButton.addEventListener('click', function() {
        const template = `
            <div class="product-item grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Producto</label>
                    <select name="products[${productCount}][product_id]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Seleccione un producto</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                    <input type="number" name="products[${productCount}][quantity]" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                    <input type="number" name="products[${productCount}][unit_price]" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove-product inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Eliminar
                    </button>
                </div>
            </div>
        `;
        productsContainer.insertAdjacentHTML('beforeend', template);
        productCount++;
    });

    // Eliminar producto
    productsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product') || e.target.closest('.remove-product')) {
            const productItem = e.target.closest('.product-item');
            if (productsContainer.children.length > 1) {
                productItem.remove();
            }
        }
    });
});
</script>
@endpush
@endsection