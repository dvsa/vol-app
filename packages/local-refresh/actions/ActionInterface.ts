export default interface ActionInterface {
  /**
   * Prompt the user for input.
   *
   * @return {Promise<boolean>} Whether the action should be executed.
   */
  prompt(): Promise<boolean>;

  /**
   * Execute the action.
   */
  execute(): Promise<void>;
}
