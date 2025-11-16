export interface User {
  _id?: string;
  username: string;
  password: string; // Plain text for admin viewing
  createdAt: string;
  isAdmin: boolean;
}

export interface Product {
  _id?: string;
  id: number;
  productName: string;
  price: number;
  quantity: number;
  username: string;
  createdAt?: Date;
  updatedAt?: Date;
}

export interface SessionUser {
  username: string;
  isAdmin: boolean;
}

