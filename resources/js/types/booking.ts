export interface Booking {
    id: number;
    booking_number: string;
    service_id: number;
    staff_id?: number;
    customer_id: number;
    business_id?: number;
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
    business?: Business;
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
    business_id?: number;
    is_active: boolean;
    booking_mode: 'service' | 'hotel' | 'event' | 'online' | 'rental' | 'chauffeur';
    metadata?: Record<string, any>;
    business?: Business;
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

export interface Business {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    is_active: boolean;
    metadata?: Record<string, any>;
    working_hours?: {
        [key: string]: {
            is_closed: boolean;
            start: string;
            end: string;
        };
    };
    created_at: string;
    updated_at: string;
}
