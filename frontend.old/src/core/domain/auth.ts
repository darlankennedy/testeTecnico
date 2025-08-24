export class Credentials {
  constructor(
    public readonly email: string,
    public readonly password: string
  ) {}

  isValid(): boolean {
    return this.email.includes('@') && this.password.length >= 6
  }
}
