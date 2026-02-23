export interface Booking {
    id: number;
    booking_number: string;
    service_id: number;
    staff_id?: number;
    customer_id: number;
    location_id?: number;
    status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show';
    booking_mode: 'service' | 'hotel' | 'event' | 'online' | 'rental' | 'chauffeur';
    start_time: string;
    end_time: string;
    duration: number;
    participants_count: number;
    is_group: boolean;
    is_recurring: boolean;
    recurring_pattern?: 'daily' | 'weekly' | 'monthly' | 'custom';
    recurring_end_date?: string;
    parent_booking_id?: number;
    price: number;
    deposit: number;
    total_price: number;
    currency: string;
    payment_status: 'pending' | 'paid' | 'partial' | 'refunded';
    payment_method?: string;
    notes?: string;
    metadata?: Record<string, any>;
    service?: Service;
    staff?: Staff;
    customer?: Customer;
    location?: Location;
    created_at: string;
    updated_at: string;
}

export interface Service {
    id: number;
    name: string;
    description?: string;
    duration: number;
    price: number;
    category_id?: number;
    location_id?: number;
    is_active: boolean;
    booking_mode: 'service' | 'hotel' | 'event' | 'online' | 'rental' | 'chauffeur';
    buffer_time_before: number;
    buffer_time_after: number;
    prepare_time: number;
    max_participants?: number;
    min_duration?: number;
    max_duration?: number;
    allow_custom_duration: boolean;
    allow_recurring: boolean;
    metadata?: Record<string, any>;
    location?: Location;
    created_at: string;
    updated_at: string;
}

export interface Staff {
    id: number;
    user_id?: number;
    name: string;
    email?: string;
    phone?: string;
    photo?: string;
    specialization?: string;
    is_active: boolean;
    locations?: number[];
    working_hours?: Record<string, any>;
    breaks?: Record<string, any>;
    holidays?: string[];
    bookings_count?: number;
    services?: Service[];
    bookings?: Booking[];
    created_at: string;
    updated_at: string;
}

export interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    notes?: string;
    metadata?: Record<string, any>;
    bookings_count?: number;
    bookings?: Booking[];
    created_at: string;
    updated_at: string;
}

export interface Location {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    is_active: boolean;
    metadata?: Record<string, any>;
    created_at: string;
    updated_at: string;
}
